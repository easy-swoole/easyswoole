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
        if($file == null){
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            $file = $caller['file'];
            $line = $caller['line'];
        }
        $func = Di::getInstance()->get(SysConst::TRIGGER_HANDLER);
        if($func instanceof TriggerInterface){
            $func::error($msg,$file,$line,$errorCode);
        }else{
            $debug = "Error at file[{$file}] line[{$line}] message:[{$msg}]";
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
            $debug = "Exception at file[{$throwable->getFile()}] line[{$throwable->getLine()}] message:[{$throwable->getMessage()}]";
            Logger::getInstance()->log($debug,'debug');
            Logger::getInstance()->console($debug,false);
        }
    }


}