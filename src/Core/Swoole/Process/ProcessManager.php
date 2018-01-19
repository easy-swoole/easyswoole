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

    public function addProcess(string $processClass,$async = true,...$args):string
    {
        if(class_exists($processClass)){
            $ins = new $processClass($async,...$args);
            if($ins instanceof AbstractProcess){
                $this->processList[$ins->getHash()] = $ins;
                return $ins->getHash();
            }else{
                throw new \Exception('class '.$processClass.' not AbstractProcess class');
            }
        }else{
            throw new \Exception('class '.$processClass.' not exist');
        }
    }

    public function getProcess(int $pid):?Process
    {
        if(ServerManager::getInstance()->isStart()){
            throw new \Exception('you cannot get process by pid before start server');
        }
        foreach ($this->processList as $item){
            if($item->getPid() == $pid){
                return $item;
            }
        }
        return null;
    }

    public function getProcessByHash(string $hash):?AbstractProcess
    {
        if(isset($this->processList[$hash])){
            return $this->processList[$hash];
        }
        return null;
    }

    public function write(int $pid,string $data):bool
    {
        $process = $this->getProcess($pid);
        if($process){
            return (bool)$process->write($data);
        }else{
            return false;
        }
    }

    public function read(int $pid,float $timeOut = 1.0):?string
    {
        $process = $this->getProcess($pid);
        if($process){
            $read = array($process);
            $write = [];
            $error = [];
            $ret = swoole_select($read, $write,$error, $timeOut);
            if($ret){
                return $process->read(64 * 1024);
            }else{
                return null;
            }
        }else{
            return null;
        }
    }
}