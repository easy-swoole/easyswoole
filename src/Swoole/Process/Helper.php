<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/9/19
 * Time: 下午7:11
 */

namespace EasySwoole\EasySwoole\Swoole\Process;


use EasySwoole\EasySwoole\ServerManager;

class Helper
{
    public static function addProcess(string $processName,string $processClass):bool
    {
        return ServerManager::getInstance()->getSwooleServer()->addProcess((new $processClass($processName))->getProcess());
    }
}