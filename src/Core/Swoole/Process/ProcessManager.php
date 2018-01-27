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
use EasySwoole\Core\Swoole\ServerManager;
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

    public function addProcess(string $processName,string $processClass,$async = true,array $args = []):bool
    {
        if(ServerManager::getInstance()->isStart()){
            trigger_error('you can not add a process after server start');
            return false;
        }
        $key = md5($processName);
        if(!isset($this->processList[$key])){
            try{
                $process = new $processClass($processName,$async,$args);
                $this->processList[$key] = $process;
                return true;
            }catch (\Throwable $throwable){
                trigger_error($throwable->getMessage().$throwable->getTraceAsString());
                return false;
            }
        }else{
            trigger_error('you can not add the same name process : '.$processName);
            return false;
        }
    }

    public function getProcessByName(string $processName):?AbstractProcess
    {
        $key = md5($processName);
        if(isset($this->processList[$key])){
            return $this->processList[$key];
        }else{
            return null;
        }
    }


    public function getProcessByPid(int $pid):?AbstractProcess
    {
        $table = TableManager::getInstance()->get('process_hash_map');
        foreach ($table as $key => $item){
            if($item['pid'] == $pid){
                return $this->processList[$key];
            }
        }
        return null;
    }


    public function setProcess(string $processName,AbstractProcess $process)
    {
        $key = md5($processName);
        $this->processList[$key] = $process;
    }

    public function reboot(string $processName):bool
    {
        $p = $this->getProcessByName($processName);
        if($p){
            \swoole_process::kill($p->getPid(),SIGTERM);
            return true;
        }else{
            return false;
        }
    }

    public function writeByProcessName(string $name,string $data):bool
    {
        $process = $this->getProcessByName($name);
        if($process){
            return (bool)$process->getProcess()->write($data);
        }else{
            return false;
        }
    }

    public function readByProcessName(string $name,float $timeOut = 0.1):?string
    {
        $process = $this->getProcessByName($name);
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