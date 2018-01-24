<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午3:19
 */

namespace EasySwoole\Core\Component\Rpc\Server;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Cache\Cache;
use EasySwoole\Core\Component\Spl\SplArray;
use EasySwoole\Core\Swoole\ServerManager;


class ServiceManager
{
    use Singleton;
    private $cacheKey = '__RpcService';

    public function addServiceNode(ServiceNode $bean):void
    {
        $key = "{$this->cacheKey}.{$bean->getServiceName()}.{$bean->getServiceId()}";
        Cache::getInstance()->set($key,$bean);
    }

    public function deleteServiceNode(ServiceNode $bean):void
    {
        $key = "{$this->cacheKey}.{$bean->getServiceName()}.{$bean->getServiceId()}";
        Cache::getInstance()->del($key);
    }

    public function allServiceNodes():?array
    {
        return Cache::getInstance()->get($this->cacheKey);
    }

    public function allService():?array
    {
        $list = $this->allServiceNodes();
        if(is_array($list)){
            return array_keys($list);
        }else{
            return null;
        }
    }

    public function deleteService(string $serviceName):void
    {
        $key = "{$this->cacheKey}.{$serviceName}";
        Cache::getInstance()->del($key);
    }

    public function getServiceNodes(string $serviceName):?array
    {
        $key = "{$this->cacheKey}.{$serviceName}";
        return Cache::getInstance()->get($key);
    }

    /*
     * 随机获得一个服务的节点
     */
    public function getServiceNode(string $serviceName):?ServiceNode
    {
        $list = $this->getServiceNodes($serviceName);
        if(is_array($list)){
            $list = array_values($list);
            return $list[mt_rand(0,count($list)-1)];
        }
        return null;
    }

    public function getServiceNodeById(string $serviceName,string $id):?ServiceNode
    {
        $key = "{$this->cacheKey}.{$serviceName}.{$id}";
        return Cache::getInstance()->get($key);
    }

    public function deleteServiceById(string $serviceName,string $id)
    {
        $key = "{$this->cacheKey}.{$serviceName}.{$id}";
        Cache::getInstance()->del($key);
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
                            $this->deleteServiceById($item->getServiceName(),$item->getServiceId());
                        }
                    }
                }
            }
        }
        return $failList;
    }
}