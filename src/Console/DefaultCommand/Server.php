<?php
/**
 * Created by PhpStorm.
 * User: eValor
 * Date: 2018/10/30
 * Time: 上午11:03
 */

namespace EasySwoole\EasySwoole\Console\DefaultCommand;

use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Console\CommandInterface;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

/**
 * 服务管理
 * Class Server
 * @package EasySwoole\EasySwoole\Console\DefaultCommand
 */
class Server implements CommandInterface
{
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
                default :
                    $response->setMessage("action {$actionName} not supported!");
            }
        }
    }

    public function help(Caller $caller, Response $response)
    {

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
        $response->setMessage($this->arrayToString(ServerManager::getInstance()->getSwooleServer()->stats()));
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
        $response->setMessage($this->arrayToString($list));
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
        } else {
            $info = [];
        }
        $response->setMessage($this->arrayToString($info));
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
        $conf = Config::getInstance()->getConf('MAIN_SERVER');
        $str = "serverName\t\tserverType\t\thost\t\tport\n";
        $str .= sprintf("%-16s\t%-16s\t%-16s%s", 'mainServer', $conf['SERVER_TYPE'], $conf['HOST'], $conf['PORT']) . "\n";
        $list = ServerManager::getInstance()->getSubServerRegister();
        foreach ($list as $serverName => $item) {
            $type = $item['type'] % 2 > 0 ? 'SWOOLE_TCP' : 'SWOOLE_UDP';
            $str .= sprintf("%-16s\t%-16s\t%-16s%s", $serverName, $type, $item['host'], $item['port']) . "\n";
        }
        $response->setMessage($str);
    }

    private function pushLog(Caller $caller, Response $response)
    {
        $args = $caller->getArgs();
        $command = array_shift($args);
        if ($command == 'enable') {
            Config::getInstance()->setDynamicConf('CONSOLE.PUSH_LOG', true);
            $str = 'enable console push log';
        } else if ($command == 'disable') {
            Config::getInstance()->setDynamicConf('CONSOLE.PUSH_LOG', false);
            $str = 'disable console push log';
        } else {
            $status = Config::getInstance()->getDynamicConf('CONSOLE.PUSH_LOG');
            $str = 'console push log is ' . ($status ? 'enable' : 'disable');
        }
        $response->setMessage($str);
    }

    /**
     * 将数组转为字符串
     * @param array $array
     * @return string
     * @author: eValor < master@evalor.cn >
     */
    private function arrayToString(array $array): string
    {
        $str = '';
        foreach ($array as $key => $value) {
            $str .= "{$key} : {$value} \n";
        }
        return trim($str);
    }
}