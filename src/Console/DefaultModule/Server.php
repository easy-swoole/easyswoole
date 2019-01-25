<?php
/**
 * Created by PhpStorm.
 * User: eValor
 * Date: 2018/10/30
 * Time: 上午11:03
 */

namespace EasySwoole\EasySwoole\Console\DefaultModule;

use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Console\ModuleInterface;
use EasySwoole\EasySwoole\Console\ConsoleService;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;
use EasySwoole\Utility\ArrayToTextTable;

/**
 * 服务管理
 * Class Server
 * @package EasySwoole\EasySwoole\Console\DefaultCommand
 */
class Server implements ModuleInterface
{
    function moduleName(): string
    {
        // TODO: Implement moduleName() method.
        return 'server';
    }

    /**
     * 执行一条管理命令
     * @param Caller $caller
     * @param Response $response
     * @author: eValor < master@evalor.cn >
     */
    public function exec(Caller $caller, Response $response)
    {
        $args = $caller->getArgs();
        if (empty($args)) {
            $this->status($caller, $response);
        } else {
            $actionName = array_shift($args);
            $caller->setArgs($args);
            switch ($actionName) {
                case 'status': $this->status($caller, $response); break;
                case 'hostIp': $this->hostIp($caller, $response); break;
                case 'reload': $this->reload($caller, $response); break;
                case 'shutdown': $this->shutdown($caller, $response); break;
                case 'close': $this->close($caller, $response); break;
                case 'clientInfo': $this->clientInfo($caller, $response); break;
                case 'serverList': $this->serverList($caller, $response); break;
                case 'pushLog' : $this->pushLog($caller,$response); break;
                default :
                    $response->setMessage("action {$actionName} not supported!");
            }
        }
    }

    public function help(Caller $caller, Response $response)
    {
        $help = <<<HELP
        
进行服务端的管理

用法: 命令 [命令参数]

status                    | 查看服务当前的状态
hostIp                    | 显示服务当前的IP地址
reload                    | 重载服务端
shutdown                  | 关闭服务端
close                     | 断开远程连接
clientInfo [fd]           | 查看某个链接的信息
serverList                | 查看服务端启动的服务列表
pushLog [enable|disable]  | 打开或关闭远程日志推送

HELP;
        $response->setMessage($help);
    }

    /**
     * 获取服务状态
     * @example server (不带任何参数的时候默认为显示状态)
     * @example server status
     * @param Caller $caller
     * @param Response $response
     * @author: eValor < master@evalor.cn >
     */
    private function status(Caller $caller, Response $response)
    {
        $stats = ServerManager::getInstance()->getSwooleServer()->stats();
        $message = new ArrayToTextTable([ $stats ]);
        $response->setMessage($message);
    }

    /**
     * 当前服务的IP列表
     * @example server hostIp
     * @param Caller $caller
     * @param Response $response
     * @author: eValor < master@evalor.cn >
     */
    private function hostIp(Caller $caller, Response $response)
    {
        $list = swoole_get_local_ip();
        $message = new ArrayToTextTable([ $list ]);
        $response->setMessage($message);
    }

    /**
     * 重启当前服务
     * @example server reload
     * @param Caller $caller
     * @param Response $response
     * @author: eValor < master@evalor.cn >
     */
    private function reload(Caller $caller, Response $response)
    {
        ServerManager::getInstance()->getSwooleServer()->reload();
        $response->setMessage('reload at' . time());
    }

    /**
     * 关闭当前服务
     * @example server shutdown
     * @param Caller $caller
     * @param Response $response
     * @author: eValor < master@evalor.cn >
     */
    private function shutdown(Caller $caller, Response $response)
    {
        ServerManager::getInstance()->getSwooleServer()->shutdown();
        $response->setMessage('shutdown at' . time());
    }

    /**
     * 断开当前链接
     * @example server close
     * @param Caller $caller
     * @param Response $response
     * @author: eValor < master@evalor.cn >
     */
    private function close(Caller $caller, Response $response)
    {
        $response->setMessage("Bye Bye !");
        $response->setStatus(Response::STATUS_RESPONSE_AND_CLOSE);
    }

    /**
     * 获取某个链接上来的客户信息
     * @example server clientInfo 1
     * @param Caller $caller
     * @param Response $response
     * @author: eValor < master@evalor.cn >
     */
    private function clientInfo(Caller $caller, Response $response)
    {
        $args = $caller->getArgs();
        $fd = array_shift($args);
        if (!empty($fd)) {
            $info = ServerManager::getInstance()->getSwooleServer()->getClientInfo($fd);
            $info = new ArrayToTextTable([ $info ]);
        } else {
            $info = 'missing parameter usage: server clientInfo fd';
        }
        $response->setMessage($info);
    }

    /**
     * 获取服务端当前已注册的端口和服务
     * @example server serverList
     * @param Caller $caller
     * @param Response $response
     * @author: eValor < master@evalor.cn >
     */
    private function serverList(Caller $caller, Response $response)
    {
        $serverInfo = [];
        $conf = Config::getInstance()->getConf('MAIN_SERVER');
        $serverInfo[] = [ 'serverName' => 'mainServer', 'serverType' => $conf['SERVER_TYPE'], 'serverHost' => $conf['LISTEN_ADDRESS'], 'listenPort' => $conf['PORT'] ];
        $list = ServerManager::getInstance()->getSubServerRegister();
        foreach ($list as $serverName => $item) {
            $type = $item['type'] % 2 > 0 ? 'SWOOLE_TCP' : 'SWOOLE_UDP';
            $serverInfo[] = [ 'serverName' => $serverName, 'serverType' => $type, 'listenAddress' => $item['listenAddress'], 'listenPort' => $item['port'] ];
        }
        $info = new ArrayToTextTable($serverInfo);
        $response->setMessage($info);
    }

    /**
     * 日志推送功能
     * @param Caller $caller
     * @param Response $response
     * @author: eValor < master@evalor.cn >
     */
    private function pushLog(Caller $caller, Response $response)
    {
        $args = $caller->getArgs();
        $command = array_shift($args);
        if ($command == 'enable') {
            ConsoleService::getInstance()->authTable->set($caller->user,[
                'pushLogTemp'=>1
            ]);
            $str = 'enable console push log';
        } else if ($command == 'disable') {
            ConsoleService::getInstance()->authTable->set($caller->user,[
                'pushLogTemp'=>0
            ]);
            $str = 'disable console push log';
        } else {
            $status = ConsoleService::getInstance()->authTable->get($caller->user)['pushLogTemp'];
            $str = 'console push log is ' . ($status ? 'enable' : 'disable');
        }
        $response->setMessage($str);
    }
}