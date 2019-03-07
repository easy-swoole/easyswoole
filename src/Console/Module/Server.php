<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-03-05
 * Time: 22:15
 */

namespace EasySwoole\EasySwoole\Console\Module;


use EasySwoole\Console\ModuleInterface;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Swoole\Task\TaskManager;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;
use EasySwoole\Utility\ArrayToTextTable;

class Server implements ModuleInterface
{

    public function moduleName(): string
    {
        // TODO: Implement moduleName() method.
        return 'server';
    }

    public function exec(Caller $caller, Response $response)
    {
        // TODO: Implement exec() method.
        $args = $caller->getArgs();
        $actionName = array_shift($args);
        $caller->setArgs($args);
        switch ($actionName) {
            case 'status': $this->status($caller, $response); break;
            case 'hostIp': $this->hostIp($caller, $response); break;
            case 'reload': $this->reload($caller, $response); break;
            case 'shutdown': $this->shutdown($caller, $response); break;
            case 'close': $this->close($caller, $response); break;
            case 'clientInfo': $this->clientInfo($caller, $response); break;
            default :
                $this->help($caller,$response);
        }
    }

    public function help(Caller $caller, Response $response)
    {
        // TODO: Implement help() method.
        $help = <<<HELP
进行服务端的管理

用法: 命令 [命令参数]

server status                    | 查看服务当前的状态
server hostIp                    | 显示服务当前的IP地址
server reload                    | 重载服务端
server shutdown                  | 关闭服务端
server clientInfo [fd]           | 查看某个链接的信息
server close [fd]                | 断开某个链接
HELP;
        $response->setMessage($help);
    }

    private function status(Caller $caller, Response $response)
    {
        $list = ServerManager::getInstance()->getSwooleServer()->stats();
        $temp = [];
        foreach ($list as $key => $item){
            $temp[] = [
                'Item'=>$key,
                'Value'=>$item
            ];
        }
        $message = new ArrayToTextTable($temp);
        $response->setMessage($message);
    }

    private function hostIp(Caller $caller, Response $response)
    {
        $list = swoole_get_local_ip();
        $temp = [];
        foreach ($list as $key => $item){
            $temp[] = [
                'Item'=>$key,
                'Value'=>$item
            ];
        }
        $message = new ArrayToTextTable($temp);
        $response->setMessage($message);
    }

    private function reload(Caller $caller, Response $response)
    {
        ServerManager::getInstance()->getSwooleServer()->reload();
        $response->setMessage('服务已经重启于:' . date('Y-m-d h:i:s'));
    }

    private function shutdown(Caller $caller, Response $response)
    {
        ServerManager::getInstance()->getSwooleServer()->shutdown();
        $response->setMessage('服务已经关闭于:' . date('Y-m-d h:i:s'));
    }

    private function clientInfo(Caller $caller, Response $response)
    {
        $args = $caller->getArgs();
        $fd = array_shift($args);
        if (!empty($fd)) {
            $list = ServerManager::getInstance()->getSwooleServer()->getClientInfo($fd);
            $temp = [];
            foreach ($list as $key => $item){
                $temp[] = [
                    'Item'=>$key,
                    'Value'=>$item
                ];
            }
            $message = new ArrayToTextTable($temp);
            $info = new ArrayToTextTable($list);
        } else {
            $info = '缺少fd参数，用法: server clientInfo {fd}';
        }
        $response->setMessage($info);
    }

    private function close(Caller $caller, Response $response)
    {
        $args = $caller->getArgs();
        $fd = array_shift($args);
        if (!empty($fd)) {
            if(ServerManager::getInstance()->getSwooleServer()->exist($fd)){
                $info = "已断开于fd:{$fd} 的链接";
            }else{
                $info = "当前fd:{$fd}不存在";
            }
        } else {
            $info = '缺少fd参数，用法: server close {fd}';
        }
        $response->setMessage($info);
    }
}