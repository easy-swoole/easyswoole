<?php


namespace EasySwoole\EasySwoole\Process;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Singleton;
use EasySwoole\Component\TableManager;
use Swoole\Process;
use Swoole\Server;
use Swoole\Table;

class Manager
{
    use Singleton;

    protected $processList = [];
    protected $table;

    function __construct()
    {
        TableManager::getInstance()->add(AbstractProcess::PROCESS_TABLE_NAME,[
            'pid'=>[
                'type'=>Table::TYPE_INT,
                'size'=>10,
            ],
            'name'=>[
                'type'=>Table::TYPE_STRING,
                'size'=>45,
            ],
            'group'=>[
                'type'=>Table::TYPE_STRING,
                'size'=>45,
            ]
        ]);
        $this->table = TableManager::getInstance()->get(AbstractProcess::PROCESS_TABLE_NAME);
    }


    function kill($pidOrGroupName,$sig = SIGTERM)
    {
        $list = [];
        if(is_numeric($pidOrGroupName)){
            $list[] = $pidOrGroupName;
        }else{
            foreach ($this->table as $key => $value){
                if($value['group'] == $pidOrGroupName){
                    $list[] = $value['pid'];
                }
            }
        }

        foreach ($list as $pid){
            Process::kill($pid,$sig);
        }
    }

    function info($pidOrGroupName = null)
    {
        $list = [];
        if($pidOrGroupName == null){
            foreach ($this->table as $pid =>$value){
                $list[$pid] = $value;
            }
        }else if(is_numeric($pidOrGroupName)){
            $info = $this->table->get($pidOrGroupName);
            if($info){
                $list[$pidOrGroupName] = $info;
            }
        }else{
            foreach ($this->table as $key => $value){
                if($value['group'] == $pidOrGroupName){
                    $list[$key] = $value;
                }
            }
        }

        return $list;
    }

    function addProcess(AbstractProcess $process)
    {
        $this->processList[] = $process;;
        return $this;
    }

    function attachToServer(Server $server)
    {
        /** @var AbstractProcess $process */
        foreach ($this->processList as $process)
        {
            $server->addProcess($process->getProcess());
        }
    }
}