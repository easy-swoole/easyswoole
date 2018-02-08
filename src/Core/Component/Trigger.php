<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/8
 * Time: 下午1:12
 */

namespace EasySwoole\Core\Component;


use EasySwoole\Core\AbstractInterface\TriggerInterface;

class Trigger
{
    public static function error($msg,$file = null,$line = null,$errorCode = E_USER_ERROR)
    {
        $func = Di::getInstance()->get(SysConst::TRIGGER_HANDLER);
        if($func instanceof TriggerInterface){
            $func::error($msg,$file,$line,$errorCode);
        }else{
            $debug = "file[{$file}] line[{$line}] message:[{$msg}]";
            Logger::getInstance()->log($debug,'debug');
            Logger::getInstance()->console($debug,false);
        }
    }

    public static function throwable(\Throwable $throwable)
    {
        $func = Di::getInstance()->get(SysConst::TRIGGER_HANDLER);
        if($func instanceof TriggerInterface){
            $func::throwable($throwable);
        }else{
            $debug = "file[{$throwable->getFile()}] line[{$throwable->getLine()}] message:[{$throwable->getMessage()}]";
            Logger::getInstance()->log($debug,'debug');
            Logger::getInstance()->console($debug,false);
        }
    }

}