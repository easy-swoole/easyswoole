<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/11
 * Time: 下午9:00
 */

namespace EasySwoole\Core\Swoole\Process;


use EasySwoole\Core\AbstractInterface\Singleton;
use \Swoole\Process;

class ProcessManager
{
    use Singleton;

    private $processList = [];

    public function addProcess(string $processClass):string
    {
        if(class_exists($processClass)){
            $ins = new $processClass;
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
                return $process->read();
            }else{
                return null;
            }
        }else{
            return null;
        }
    }
}