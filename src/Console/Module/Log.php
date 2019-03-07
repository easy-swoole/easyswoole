<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-03-07
 * Time: 13:57
 */

namespace EasySwoole\EasySwoole\Console\Module;


use EasySwoole\Console\Console;
use EasySwoole\Console\ModuleInterface;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

class Log implements ModuleInterface
{

    public function moduleName(): string
    {
        // TODO: Implement moduleName() method.
        return 'log';
    }

    public function exec(Caller $caller, Response $response)
    {
        // TODO: Implement exec() method.
        $arg = $caller->getArgs();
        $action = array_shift($arg);
        switch ($action){
            case 'enable':{
                break;
            }
            case 'disable':{
                break;
            }
            case 'category':{
                break;
            }
            case 'setCategory':{
                break;
            }
            case 'clearCategory':{
                break;
            }
            default:{
                $this->help($caller,$response);
            }
        }
    }

    public function help(Caller $caller, Response $response)
    {
        $help = <<<HELP
        
远程控制台日志推送管理
用法 : 
    log enable 开启日志推送
    log disable 关闭日志推送
    log category 查看当前推送分类
    log setCategory {category} 仅推送某分类日志
    log clearCategory 清除推送分类限制
HELP;
        $response->setMessage($help);
    }

    public static function push(string $str, $logCategory = null)
    {
        $fd = Auth::currentFd();
        if($fd > 0){
            Console::getInstance()->send($fd,$str);
        }
    }
}