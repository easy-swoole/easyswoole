<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/25
 * Time: 上午10:10
 */

namespace EasySwoole\Core\Component\Cluster\Common;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Cache\Cache;

class Config
{
    private $cacheKey = '__clusterConf';

    use Singleton;

    function __construct()
    {
        $conf = \EasySwoole\Config::getInstance()->getConf('CLUSTER');
        Cache::getInstance()->set($this->cacheKey,$conf);
    }

    function get($key)
    {
        $key = "{$this->cacheKey}.{$key}";
        return  Cache::getInstance()->get($key);
    }

    function set($key,$data)
    {
        $key = "{$this->cacheKey}.{$key}";
        Cache::getInstance()->set($key,$data);
    }
}