<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/29
 * Time: 10:08 PM
 */

namespace EasySwoole\EasySwoole\Console\DefaultCommand;

use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Console\CommandInterface;
use EasySwoole\EasySwoole\Swoole\Memory\TableManager;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

/**
 * 鉴权认证
 * Class Auth
 * @package EasySwoole\EasySwoole\Console\DefaultCommand
 */
class Auth implements CommandInterface
{
    /**
     * 执行鉴权
     * @example auth password
     * @param Caller $caller
     * @param Response $response
     * @author: eValor < master@evalor.cn >
     */
    public function exec(Caller $caller, Response $response)
    {
        $fd = $caller->getClient()->getFd();
        $args = $caller->getArgs();
        if (Config::getInstance()->getConf('CONSOLE.AUTH') == array_shift($args)) {
            TableManager::getInstance()->get('Console.Auth')->set($fd, [
                'isAuth'   => 1,
                'tryTimes' => 0
            ]);
            $response->setMessage('auth succeed');
        } else {
            $info = TableManager::getInstance()->get('Console.Auth')->get($fd);
            if (!empty($info)) {
                if ($info['tryTimes'] > 5) {
                    $response->setStatus(Response::STATUS_RESPONSE_AND_CLOSE);
                } else {
                    TableManager::getInstance()->get('Console.Auth')->set($fd, [
                        'isAuth'   => 0,
                        'tryTimes' => $info['tryTimes'] + 1
                    ]);
                }
            } else {
                TableManager::getInstance()->get('Console.Auth')->set($fd, [
                    'isAuth'   => 0,
                    'tryTimes' => 1
                ]);
            }
            $response->setMessage('auth fail');
        }
    }

    /**
     * 获取帮助
     * @example help auth
     * @param Caller $caller
     * @param Response $response
     * @author: eValor < master@evalor.cn >
     */
    public function help(Caller $caller, Response $response)
    {
        $help = <<<HELP
        
在服务端设置需要授权才能连接时，可以使用本命令进行授权
用法 : auth password

HELP;
        $response->setMessage($help);
    }
}