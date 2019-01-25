<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/29
 * Time: 10:08 PM
 */

namespace EasySwoole\EasySwoole\Console\DefaultModule;


use EasySwoole\EasySwoole\Console\ModuleInterface;
use EasySwoole\EasySwoole\Console\ConsoleService;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

/**
 * 鉴权认证
 * Class Auth
 * @package EasySwoole\EasySwoole\Console\DefaultCommand
 */
class Auth implements ModuleInterface
{
    public function moduleName(): string
    {
        // TODO: Implement moduleName() method.
        return 'auth';
    }

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
        $user = array_shift($args);
        $password = array_shift($args);
        $info = ConsoleService::getInstance()->authTable->get($user);
        if(!empty($info) && $info['password'] === $password){
            ConsoleService::getInstance()->authTable->set($user,[
                'fd'=>$fd
            ]);
            $response->setMessage('auth success');
        }else{
            $response->setStatus($response::STATUS_RESPONSE_AND_CLOSE);
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
用法 : auth {user} {password}

HELP;
        $response->setMessage($help);
    }
}