<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/29
 * Time: 下午3:37
 */

namespace EasySwoole\EasySwoole\Swoole\Process;


use EasySwoole\EasySwoole\Swoole\Time\Timer;
use Swoole\Process;

abstract class AbstractProcess
{
    private $swooleProcess;
    private $processName;
    private $async = null;
    private $args = [];

    final function __construct(string $processName,array $args = [],$async = true)
    {
        $this->async = $async;
        $this->args = $args;
        $this->processName = $processName;
        $this->swooleProcess = new \swoole_process([$this,'__start']);
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
            return null;
        }
    }

    function __start(Process $process)
    {
        if(PHP_OS != 'Darwin'){
            $process->name($this->getProcessName());
        }

        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);
        }

        Process::signal(SIGTERM,function ()use($process){
            $this->onShutDown();
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
    public abstract function onReceive(string $str);

}