<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/20
 * Time: 下午11:24
 */

namespace EasySwoole\EasySwoole\Console;


use EasySwoole\Component\Singleton;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Console\DefaultModule\Auth;
use EasySwoole\EasySwoole\Console\DefaultModule\Help;
use EasySwoole\EasySwoole\Console\DefaultModule\Server;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Socket\Bean\Response;
use EasySwoole\Socket\Config;
use EasySwoole\Socket\Dispatcher;
use Swoole\Table;
use EasySwoole\EasySwoole\Config as GlobalConfig;

class ConsoleService
{
    use Singleton;
    /*
    * 下划线开头表示不希望用户使用
    */
    public $authTable = '__Console.Auth';

    function __construct()
    {
        $config = GlobalConfig::getInstance()->getConf('CONSOLE');
        TableManager::getInstance()->add('__Console.Auth', [
            'user' => ['type' => Table::TYPE_STRING, 'size' => 20],
            'password' => ['type' => Table::TYPE_STRING, 'size' => 20],
            'fd' => ['type' => Table::TYPE_INT, 'size' => 4],
            'isAuth' => ['type' => Table::TYPE_INT, 'size' => 1],
            'pushLog' => ['type' => Table::TYPE_INT, 'size' => 1],
            'pushLogTemp'=>['type' => Table::TYPE_INT, 'size' => 1],
            'modules' => ['type' => Table::TYPE_STRING, 'size' => 1024],
        ],32);
        $this->authTable = TableManager::getInstance()->get('__Console.Auth');

        if(is_array($config['AUTH'])){
            foreach ($config['AUTH'] as $item){
                $modules = $item['MODULES'];
                if(in_array('auth',$modules)){
                    $modules[] = 'auth';
                }
                $this->authTable->set($item['USER'],[
                    'user'=>$item['USER'],
                    'password'=>$item['PASSWORD'],
                    'modules'=>serialize($modules),
                    'pushLog'=>intval($item['PUSH_LOG']),
                    'pushLogTemp'=>1,
                    'isAuth'=>0
                ]);
            }
        }
    }

    public function push(string $string)
    {
        /*
         * 服务启动前不做push.为cli单元测试预备
         */
        if(!ServerManager::getInstance()->isStart()){
            return;
        }
        $string = ConsoleProtocolParser::pack(serialize($string));
        $server = ServerManager::getInstance()->getSwooleServer();
        foreach ($this->authTable as $key => $value){
            if($value['pushLog'] && $value['pushLogTemp']){
                $server->send($value['fd'],$string);
            }
        }
    }

    /*
     * 注册默认的命令
     */
    private function registerDefault()
    {
        ModuleContainer::getInstance()->set(new Help());
        ModuleContainer::getInstance()->set(new Auth());
        ModuleContainer::getInstance()->set(new Server());
    }

    public function __registerTcpServer()
    {
        $config = GlobalConfig::getInstance()->getConf('CONSOLE');
        if ($config['ENABLE']) {
            $this->registerDefault();
            $conf = new Config();
            $conf->setParser(new ConsoleProtocolParser());
            $conf->setType($conf::TCP);
            $conf->setOnExceptionHandler(function ($server, \Throwable $throwable, $raw, $client, Response $response) {
                $response->setMessage($throwable->getMessage());
                $response->setStatus($response::STATUS_RESPONSE_AND_CLOSE);
            });

            $dispatcher = new Dispatcher($conf);
            $sub = ServerManager::getInstance()->addServer('ConsoleTcp', $config['PORT'], SWOOLE_TCP, $config['LISTEN_ADDRESS'], [
                'heartbeat_check_interval' => $config['EXPIRE'],
                'heartbeat_idle_time' => $config['EXPIRE'],
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
                'package_max_length' => 1024 * 1024
            ]);

            $sub->set($sub::onReceive, function (\swoole_server $server, $fd, $reactor_id, $data) use ($dispatcher) {
                $dispatcher->dispatch($server, $data, $fd, $reactor_id);
            });

            $sub->set($sub::onConnect, function (\swoole_server $server, int $fd, int $reactorId) {
                $hello = 'Welcome to ' . GlobalConfig::getInstance()->getConf('SERVER_NAME') . ' !,please auth : auth {user} {password}';
                $server->send($fd, ConsoleProtocolParser::pack(serialize($hello)), $reactorId);
            });

            $sub->set($sub::onClose, function (\swoole_server $server, int $fd, int $reactorId) {
                foreach ($this->authTable as $key => $value){
                    if($value['fd'] === $fd){
                        $this->authTable->set($key,[
                            'fd'=>null
                        ]);
                    }
                }
            });
        }
    }
}