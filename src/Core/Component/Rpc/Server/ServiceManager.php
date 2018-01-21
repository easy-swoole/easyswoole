<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午3:19
 */

namespace EasySwoole\Core\Component\Rpc;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Cache\Cache;


class ServiceManager
{
    use Singleton;
    private const cacheKey = '__RpcService';

    public function addServiceNode(ServiceNode $bean):void
    {
        $key = "{self::cacheKey}.{$bean->getServerName()}.{$bean->getServiceId()}";
        Cache::getInstance()->set($key,$bean);
    }

    public function deleteServiceNode(ServiceNode $bean):void
    {
        $key = "{self::cacheKey}.{$bean->getServerName()}.{$bean->getServiceId()}";
        Cache::getInstance()->del($key);
    }

    public function allServiceNodes():?array
    {
        return Cache::getInstance()->get(self::cacheKey);
    }

    public function allService()
    {

    }



    public function deleteService(string $serviceName):void
    {
        $key = "{self::cacheKey}.{$serviceName}";
        Cache::getInstance()->del($key);
    }

    public function getServiceNodes(string $serviceName):?array
    {
        $key = "{self::cacheKey}.{$serviceName}";
        return Cache::getInstance()->get($key);
    }

    public function getServiceNode(string $serviceName):?ServiceNode
    {
        $list = $this->getServiceNodes($serviceName);
        if(is_array($list)){
            return array_rand($list);
        }else{
            return null;
        }
    }
}