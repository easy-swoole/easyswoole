<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-03-05
 * Time: 22:15
 */

namespace EasySwoole\EasySwoole\Console\Module;


use EasySwoole\Component\TableManager;
use EasySwoole\Console\ConsoleInterceptor;
use EasySwoole\Console\ModuleInterface;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;
use Swoole\Table;

class Auth implements ModuleInterface
{
    public static $authTable;
    private static $user;
    protected $config;
    public function __construct()
    {
        $this->config = Config::getInstance()->getConf('CONSOLE');
        self::$user = $this->config['USER'];
        TableManager::getInstance()->add('__Console.Auth', [
            'fd' => ['type' => Table::TYPE_INT, 'size' => 4],
        ],4);
        self::$authTable = TableManager::getInstance()->get('__Console.Auth');
        ConsoleInterceptor::getInstance()->set(function (Caller $caller,Response $response){
            if(in_array($caller->getAction(),['q','quit'])){
                $response->setMessage('bye bye!!!');
                $response->setStatus($response::STATUS_RESPONSE_AND_CLOSE);
                self::$authTable->set($this->config['USER'],['fd'=>null]);
                return false;
            }else if($caller->getAction() == 'help'){
                return true;
            }else if($caller->getAction() != 'auth'){
                $ret = $this->isAuth($caller->getClient()->getFd());
                if(!$ret){
                    $response->setMessage('please auth, auth {USER} {PASSWORD}');
                }
                return $ret;
            }
        });

    }

    public function moduleName(): string
    {
        // TODO: Implement moduleName() method.
        return 'auth';
    }

    public function exec(Caller $caller, Response $response)
    {
        // TODO: Implement exec() method.
        $info = $caller->getArgs();
        $user = array_shift($info);
        $password =  array_shift($info);
        if($user === $this->config['USER'] && $password === $this->config['PASSWORD']){
            self::$authTable->set($user,[
                'fd'=>$caller->getClient()->getFd()
            ]);
            $response->setMessage('auth success');
        }else{
            $response->setMessage('auth fail,please auth, auth {USER} {PASSWORD}');
        }
    }

    public function help(Caller $caller, Response $response)
    {
        // TODO: Implement help() method.
        $help = <<<HELP
        
在服务端设置需要授权才能连接时，可以使用本命令进行授权
用法 : auth {user} {password}
HELP;
        $response->setMessage($help);
    }

    private function isAuth(int $fd):bool
    {
        $info = self::$authTable->get(self::$user);
        if($info){
            return $info['fd'] === $fd;
        }else{
            return false;
        }
    }

    public static function currentFd():?int
    {
        if(!self::$authTable){
            return null;
        }
        $info = self::$authTable->get(self::$user);
        if($info){
            return $info['fd'];
        }else{
            return null;
        }
    }
}