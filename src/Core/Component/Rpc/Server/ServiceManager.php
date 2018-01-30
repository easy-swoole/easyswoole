<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午3:19
 */

namespace EasySwoole\Core\Component\Rpc\Server;

use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Swoole\Memory\TableManager;
use Swoole\Table;

class ServiceManager
{
    use Singleton;

    private $tableName = '__RpcService';

    function __construct()
    {
        TableManager::getInstance()->add($this->tableName,[
            'serviceName'=>[
                'size'=>35,
                'type'=>Table::TYPE_STRING
            ],
            'serviceId'=>[
                'size'=>16,
                'type'=>Table::TYPE_STRING
            ],
            'address'=>[
                'size'=>15,
                'type'=>Table::TYPE_STRING
            ],
            'port'=>[
                'size'=>10,
                'type'=>Table::TYPE_INT
            ],
            'lastHeartBeat'=>[
                'size'=>10,
                'type'=>Table::TYPE_INT
            ]
        ],4096);
    }



    public function addServiceNode(ServiceNode $bean):void
    {
        $this->getTable()->set($bean->getServiceId(),$bean->toArray());
    }



    public function deleteServiceNode(ServiceNode $bean):void
    {
        $this->getTable()->del($bean->getServiceId());
    }

    public function allServiceNodes():array
    {
        $list = [];
        foreach ($this->getTable() as $key => $item) {
            $list[$key] = $item;
        }
        return $list;
    }

    public function allService():?array
    {
        $list = [];
        $all = $this->allServiceNodes();
        foreach ($all as $item)
        {
            $list[$item['serviceName']] = true;
        }
        return array_keys($list);
    }

    public function deleteService(string $serviceName):void
    {
        $all = $this->allServiceNodes();
        foreach ($all as $key => $item){
            if($item['serviceName'] === $serviceName){
                $this->getTable()->del($key);
            }
        }
    }

    public function getServiceNodes(string $serviceName):array
    {
        $list = [];
        $all = $this->allServiceNodes();
        foreach ($all as $item){
            if($item['serviceName'] === $serviceName){
                $list[] = $item;
            }
        }
        return $list;
    }

    /*
     * 随机获得一个服务的节点
     */
    public function getServiceNode(string $serviceName):?ServiceNode
    {
        $list = $this->getServiceNodes($serviceName);
        if(!empty($list)){
            $data = $list[array_rand($list)];
            return new ServiceNode($data);
        }
        return null;
    }

    public function getServiceNodeById(string $id):?ServiceNode
    {
        $data = $this->getTable()->del($id);
        if($data){
            return new ServiceNode($data);
        }else{
            return null;
        }
    }

    public function deleteServiceById(string $id)
    {
        $this->getTable()->del($id);
    }

    public function gc($timeOut = 15):array
    {
        $failList = [];
        $time = time();
        $list = $this->allServiceNodes();
        if(is_array($list)){
            foreach ($list as $service){
                foreach ($service as $item){
                    if($item instanceof ServiceNode){
                        if($item->getAddress() == '127.0.0.1'){
                            continue;
                        }
                        if($time - $item->getLastHeartBeat() > $timeOut){
                            $failList[] = $item;
                            $this->deleteServiceById($item->getServiceId());
                        }
                    }
                }
            }
        }
        return $failList;
    }

    private function getTable():Table
    {
        return TableManager::getInstance()->get($this->tableName);
    }
}