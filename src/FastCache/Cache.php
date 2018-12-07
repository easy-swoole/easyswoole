<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/6
 * Time: 11:10 PM
 */

namespace EasySwoole\EasySwoole\FastCache;


use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\ServerManager;

class Cache
{
    use Singleton;

    function __construct()
    {
        $num = Config::getInstance()->getConf('FAST_CACHE.PROCESS_NUM');
        if($num > 0){
            $serverNAme = Config::getInstance()->getConf('SERVER_NAME');
            ServerManager::getInstance()->getSwooleServer()->addProcess((new CacheProcess("{$serverNAme}.FAST_CACHE"))->getProcess());
        }
    }
}