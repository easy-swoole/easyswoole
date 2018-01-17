<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/17
 * Time: 上午11:28
 */

namespace EasySwoole\Core\Swoole\Process;


use EasySwoole\Core\Swoole\ServerManager;
use Swoole\Process;

abstract class AbstractProcess
{
    protected $swooleProcess;
    final function __construct()
    {
        $this->swooleProcess = new \swoole_process([$this,'__start'],false,2);
        ServerManager::getInstance()->getServer()->addProcess($this->swooleProcess);
    }

    public function loadMsg(string $str)
    {

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
        return $this->getProcess()->pid;
    }

    /*
     * 默认100ms
     */
    public function setTick(callable $callback,$time = 100)
    {
        Process::signal(SIGALRM, $callback);
        Process::alarm($time * 1000);
    }

    public function clearTick()
    {
        Process::alarm(-1);
    }

    function __start()
    {
        Process::signal(SIGTERM,function (){
            $this->onShutDown();
            $this->swooleProcess->exit(0);
        });
        swoole_event_add($this->swooleProcess->pipe, function(){
            $msg = $this->swooleProcess->read(64 * 1024);
            $this->onReceive($msg);
        });
        $this->run($this->swooleProcess);
    }

    public abstract function run(Process $process);
    public abstract function onShutDown();
    public abstract function onReceive(string $str);

}