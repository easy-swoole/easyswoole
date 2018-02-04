<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/11
 * Time: 下午9:00
 */

namespace EasySwoole\Core\Swoole\Process;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Swoole\Memory\TableManager;
use Swoole\Table;

class ProcessManager
{
    use Singleton;

    private $processList = [];

    function __construct()
    {
        TableManager::getInstance()->add(
            'process_hash_map',[
                'pid'=>[
                    'type'=>Table::TYPE_INT,
                    'size'=>10
                ]
            ],256
        );
    }

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


    public function getProcessByHash(string $hash):?AbstractProcess
    {
        if(isset($this->processList[$hash])){
            return $this->processList[$hash];
        }
        return null;
    }

    public function getProcess(int $pid):?AbstractProcess
    {
        foreach ($this->processList as $item){
            if($item->getPid() == $pid){
                return $item;
            }
        }
        return null;
    }

    public function allProcess():array
    {
        return $this->processList;
    }

    public function writeByHash(string $hash,string $data):bool
    {
        $process = $this->getProcessByHash($hash);
        if($process){
            return (bool)$process->getProcess()->write($data);
        }else{
            return false;
        }
    }

    public function readByHash(string $hash,float $timeOut = 0.1):?string
    {
        $process = $this->getProcessByHash($hash);
        if($process){
            $process = $process->getProcess();
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