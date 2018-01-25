<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/17
 * Time: 上午11:28
 */

namespace EasySwoole\Core\Swoole\Process;


use EasySwoole\Core\Swoole\Memory\TableManager;
use EasySwoole\Core\Swoole\ServerManager;
use Swoole\Process;

abstract class AbstractProcess
{
    protected $swooleProcess;
    private $async = null;
    private $args = [];
    function __construct($async = true,...$args)
    {
        $this->async = $async;
        $this->args = $args;
        $this->swooleProcess = new \swoole_process([$this,'__start'],false,2);
        ServerManager::getInstance()->getServer()->addProcess($this->swooleProcess);
    }

    public function getProcess():Process
    {
        return $this->swooleProcess;
    }

    public function getHash()
    {
        return spl_object_hash($this->swooleProcess);
    }

    public function getPid()
    {
        if(empty($this->swooleProcess->pid)){
            $pid = TableManager::getInstance()->get('process_hash_map')->get($this->getHash())['pid'];
            $this->swooleProcess->pid = $pid;
            return $pid;
        }else{
            return $this->getProcess()->pid;
        }
    }

    /*
     * 默认100ms
     */
    public function setTick(callable $callback,$time = 100*1000)
    {
        Process::signal(SIGALRM, $callback);
        Process::alarm($time);
    }

    public function clearTick()
    {
        Process::alarm(-1);
    }

    function __start(Process $process)
    {
        TableManager::getInstance()->get('process_hash_map')->set(
            $this->getHash(),['pid'=>$process->pid]
        );
        pcntl_async_signals(true);
        Process::signal(SIGTERM,function (){
            $this->onShutDown();
            TableManager::getInstance()->get('process_hash_map')->del($this->getHash());
            $this->swooleProcess->exit(0);
        });
        if($this->async){
            swoole_event_add($this->swooleProcess->pipe, function(){
                $msg = $this->swooleProcess->read(64 * 1024);
                $this->onReceive($msg);
            });
        }
        $this->run($this->swooleProcess);
    }

    public function getArgs():array
    {
        return $this->args;
    }

    public abstract function run(Process $process);
    public abstract function onShutDown();
    public abstract function onReceive(string $str,...$args);

}