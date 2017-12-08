<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/2
 * Time: 下午10:04
 */

namespace EasySwoole\Core\AbstractInterface;


use EasySwoole\Core\Swoole\Server;

trait Singleton
{
    private static $instanceList = [];

    static function getInstance($saveInCoroutine = false)
    {
        if($saveInCoroutine == false){
            $cid = 0;
        }else{
            $cid = self::getInstanceId();
        }
        if(!isset(self::$instanceList[$cid])){
            $ins = new static();
            self::$instanceList[$cid] = $ins;
        }else{
            /*
            * 为了IDE提示才
            */
            $ins = self::$instanceList[$cid];
        }
        return $ins;
    }

    static function getInstanceId():int
    {
        $cid = Server::getInstance()->coroutineId();
        if($cid === null){
            $cid = 0;
        }
        return $cid;
    }

    final public function freeInstance($instanceId = null):?bool
    {
        if($instanceId === null){
            $instanceId = self::getInstanceId();
        }
        if(isset(self::$instanceList[$instanceId])){
            unset(self::$instanceList[$instanceId]);
            return true;
        }else{
            return false;
        }
    }
}