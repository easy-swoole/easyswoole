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
use EasySwoole\EasySwoole\Config;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

class Log implements ModuleInterface
{

    function __construct()
    {
        Config::getInstance()->setDynamicConf('CONSOLE.PUSH_LOG',false);
    }

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
                Config::getInstance()->setDynamicConf('CONSOLE.PUSH_LOG',true);
                $response->setMessage('已经开启日志推送');
                break;
            }
            case 'disable':{
                Config::getInstance()->setDynamicConf('CONSOLE.PUSH_LOG',false);
                $response->setMessage('已经关闭日志推送');
                break;
            }
            case 'category':{
                $category = Config::getInstance()->getDynamicConf('CONSOLE.CATEGORY');
                if(!empty($category)){
                    $response->setMessage('当前制推送分类仅为:'.$category);
                }else{
                    $response->setMessage('当前无限制推送分类');
                }
                break;
            }
            case 'setCategory':{
                $category =  array_shift($arg);
                if(!empty($category)){
                    if(!empty($category)){
                        Config::getInstance()->setDynamicConf('CONSOLE.CATEGORY',$category);
                        $response->setMessage('当前制推送分类仅为:'.$category);
                    }else{
                        $response->setMessage('分类限制不能为空');
                    }
                }
                break;
            }
            case 'clearCategory':{
                Config::getInstance()->setDynamicConf('CONSOLE.CATEGORY',null);
                $response->setMessage('已清除日志推送分类限制');
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
            if(Config::getInstance()->getDynamicConf('CONSOLE.PUSH_LOG')){
                $category = Config::getInstance()->getDynamicConf('CONSOLE.CATEGORY');
                if(!empty($category) && $category != $logCategory){
                    return;
                }
                Console::getInstance()->send($fd,$str);
            }
        }
    }
}