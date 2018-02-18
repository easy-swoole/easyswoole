<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午3:19
 */

namespace EasySwoole\Core\Component\Rpc\Server;

use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Cluster\Config;
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
            'serverId'=>[
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
            ],
            'encrypt'=>[
                'size'=>15,
                'type'=>Table::TYPE_STRING
            ],
            'token'=>[
                'size'=>32,
                'type'=>Table::TYPE_STRING
            ]
        ],2048);
    }

    public function addServiceNode(ServiceNode $bean):void
    {
        $this->getTable()->set($this->generateKey($bean),$bean->toArray());
    }

    public function deleteServiceNode(ServiceNode $bean):void
    {
        $this->getTable()->del($this->generateKey($bean));
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
        foreach ($all as $key => $item){
            if($item['serviceName'] === $serviceName){
                $list[$key] = $item;
            }
        }
        return $list;
    }

    /*
     * 随机获得一个服务的节点
     */
    public function getServiceNode(string $serviceName,$exceptId = null):?ServiceNode
    {
        $list = $this->getServiceNodes($serviceName);
        if($exceptId !== null){
            foreach ($list as $key => $item){
                if($key === $exceptId){
                    unset($list[$key]);
                    break;
                }
            }
        }
        if(!empty($list)){
            $data = $list[array_rand($list)];
            return new ServiceNode($data);
        }
        return null;
    }

    public function getServiceNodeById(string $id):?ServiceNode
    {
        $data = $this->getTable()->get($id);
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
                foreach ($service as $key => $item){
                    if($item instanceof ServiceNode){
                        //不对自身节点做gc
                        if($key === $this->generateKey($item)){
                            continue;
                        }
                        if($time - $item->getLastHeartBeat() > $timeOut){
                            $failList[$key] = $item;
                            $this->deleteServiceById($key);
                        }
                    }
                }
            }
        }
        return $failList;
    }

    public function getLocalServices():array
    {
        $result = [];
        $list = $this->allServiceNodes();
        if(is_array($list)){
            foreach ($list as $service){
                foreach ($service as $key => $item){
                    if($item instanceof ServiceNode){
                        if($key === $this->generateKey($item)){
                            $result[$key] = $item;
                        }
                    }
                }
            }
        }
        return $result;
    }

    private function getTable():Table
    {
        return TableManager::getInstance()->get($this->tableName);
    }

    private function generateKey(ServiceNode $serviceNode):string
    {
        return substr(md5($serviceNode->getServerId().$serviceNode->getServiceName()), 8, 16);
    }
}