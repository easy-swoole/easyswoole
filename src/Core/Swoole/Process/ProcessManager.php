<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/11
 * Time: 下午9:00
 */

namespace EasySwoole\Core\Swoole\Process;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Swoole\ServerManager;
use \Swoole\Process;

class ProcessManager
{
    use Singleton;

    private $processList = [];

    //自 1.9.6 版本以后，参数 $create_pipe 默认值为 2，启用$redirect_stdin_and_stdout （即 redirect_stdin_and_stdout 为 true）后强制为 1
    public function addProcess(string $processName,callable $callback, $redirect_stdin_stdout = false, $create_pipe = true):Process
    {
        $process = new Process($callback,$redirect_stdin_stdout,$create_pipe);
        $process->name($processName);
        $this->processList[$processName] = $process;
        $ret = ServerManager::getInstance()->getServer()->addProcess($process);
        if(!$ret){
            throw new \Exception("add process :{$processName}fail");
        }
        return $process;
    }

    public function getProcess(string $processName):?Process
    {
        if(isset($this->processList[$processName])){
            return $this->processList[$processName];
        }else{
            return null;
        }
    }

    public function write(string $processName,string $data):bool
    {
        $process = $this->getProcess($processName);
        if($process){
            return (bool)$process->write($data);
        }else{
            return false;
        }
    }

    public function read(string $processName,float $timeOut = 1.0):?string
    {
        $process = $this->getProcess($processName);
        if($process){
            $read = array($process);
            $write = [];
            $error = [];
            $ret = swoole_select($read, $write,$error, $timeOut);
            if($ret){
                return $process->read();
            }else{
                return null;
            }
        }else{
            return null;
        }
    }



}