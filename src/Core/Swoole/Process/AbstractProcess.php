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
use EasySwoole\Core\Swoole\Time\Timer;
use Swoole\Process;

abstract class AbstractProcess
{
    private $swooleProcess;
    private $processName;
    private $async = null;
    private $args = [];
    function __construct(string $processName,array $args,$async = true)
    {
        $this->async = $async;
        $this->args = $args;
        $this->processName = $processName;
        $this->swooleProcess = new \swoole_process([$this,'__start'],false,2);
        ServerManager::getInstance()->getServer()->addProcess($this->swooleProcess);
    }

    public function getProcess():Process
    {
        return $this->swooleProcess;
    }

    /*
     * 仅仅为了提示:在自定义进程中依旧可以使用定时器
     */
    public function addTick($ms,callable $call):?int
    {
        return Timer::loop(
            $ms,$call
        );
    }

    public function clearTick(int $timerId)
    {
        Timer::clear($timerId);
    }

    public function delay($ms,callable $call):?int
    {
        return Timer::delay(
            $ms,$call
        );
    }

    /*
     * 服务启动后才能获得到pid
     */
    public function getPid():?int
    {
        if(isset($this->swooleProcess->pid)){
            return $this->swooleProcess->pid;
        }else{
            $key = md5($this->processName);
            $pid = TableManager::getInstance()->get('process_hash_map')->get($key);
            if($pid){
                return $pid['pid'];
            }else{
                return null;
            }
        }
    }


    function __start(Process $process)
    {
        if(PHP_OS != 'Darwin'){
            $process->name($this->getProcessName());
        }
        TableManager::getInstance()->get('process_hash_map')->set(
            md5($this->processName),['pid'=>$this->swooleProcess->pid]
        );
        ProcessManager::getInstance()->setProcess($this->getProcessName(),$this);
        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);
        }
        Process::signal(SIGTERM,function ()use($process){
            $this->onShutDown();
            TableManager::getInstance()->get('process_hash_map')->del(md5($this->processName));
            swoole_event_del($process->pipe);
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

    public function getArg($key)
    {
        if(isset($this->args[$key])){
            return $this->args[$key];
        }else{
            return null;
        }
    }

    public function getProcessName()
    {
        return $this->processName;
    }

    public abstract function run(Process $process);
    public abstract function onShutDown();
    public abstract function onReceive(string $str,...$args);

}