<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/20
 * Time: 下午11:24
 */

namespace EasySwoole\EasySwoole\Console;


use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Console\DefaultCommand\Auth;
use EasySwoole\EasySwoole\Console\DefaultCommand\Help;
use EasySwoole\EasySwoole\Console\DefaultCommand\Server;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Socket\Bean\Response;
use EasySwoole\Socket\Config;
use EasySwoole\Socket\Dispatcher;
use Swoole\Table;
use EasySwoole\EasySwoole\Config as GlobalConfig;

class TcpService
{
    /*
    * 下划线开头表示不希望用户使用
    */
    public static $__swooleTableName = '__Console.Auth';

    function __construct(?array $config)
    {
        if ($config['ENABLE']) {
            //创建swoole table 用于记录客户端连接
            TableManager::getInstance()->add(self::$__swooleTableName, [
                'isAuth'   => [ 'type' => Table::TYPE_INT, 'size' => 1 ],
                'tryTimes' => [ 'type' => Table::TYPE_INT, 'size' => 1 ]
            ]);
            $this->registerDefault();
            $conf = new Config();
            $conf->setParser(new TcpParser());
            $conf->setType($conf::TCP);
            $conf->setOnExceptionHandler(function ($server, \Throwable $throwable, $raw, $client, Response $response) {
                $response->setMessage($throwable->getMessage());
                $response->setArgs([
                    'raw'       => $raw,
                    'exception' => $throwable->getTraceAsString()
                ]);
                $response->setStatus($response::STATUS_RESPONSE_AND_CLOSE);
            });

            $dispatcher = new Dispatcher($conf);
            $sub = ServerManager::getInstance()->addServer('ConsoleTcp', $config['PORT'], SWOOLE_TCP, $config['LISTEN_ADDRESS'], [
                'heartbeat_check_interval' => $config['EXPIRE'],
                'heartbeat_idle_time'      => $config['EXPIRE'],
                'open_length_check'        => true,
                'package_length_type'      => 'N',
                'package_length_offset'    => 0,
                'package_body_offset'      => 4,
                'package_max_length'       => 1024 * 1024
            ]);
            $sub->set($sub::onReceive, function (\swoole_server $server, $fd, $reactor_id, $data) use ($dispatcher) {
                $dispatcher->dispatch($server, $data, $fd, $reactor_id);
            });
            $sub->set($sub::onConnect, function (\swoole_server $server, int $fd, int $reactorId) {
                $hello = 'Hello !' . GlobalConfig::getInstance()->getConf('SERVER_NAME');
                $server->send($fd, TcpParser::pack($hello), $reactorId);
                if (GlobalConfig::getInstance()->getConf('CONSOLE.AUTH')) {
                    $server->send($fd, TcpParser::pack('please enter your auth key; auth $authKey'), $reactorId);
                } else {
                    //在不需要鉴权的时候，全部用户都是允许的
                    TableManager::getInstance()->get(self::$__swooleTableName)->set($fd, [
                        'isAuth'   => 1,
                        'tryTimes' => 0
                    ]);
                }
            });
            $sub->set($sub::onClose, function (\swoole_server $server, int $fd, int $reactorId) {
                TableManager::getInstance()->get(self::$__swooleTableName)->del($fd);
            });
        }
        GlobalConfig::getInstance()->setDynamicConf('CONSOLE.PUSH_LOG', GlobalConfig::getInstance()->getConf('CONSOLE.PUSH_LOG'));
    }

    static function push(string $string)
    {
        $table = TableManager::getInstance()->get(self::$__swooleTableName);
        if ($table instanceof Table) {
            $string = TcpParser::pack($string);
            foreach ($table as $fd => $value) {
                if (GlobalConfig::getInstance()->getConf('CONSOLE.AUTH') && $value['isAuth'] == 0) {
                    continue;
                }
                ServerManager::getInstance()->getSwooleServer()->send($fd, $string);
            }
        }
    }

    /*
     * 注册默认的命令
     */
    private function registerDefault()
    {
        CommandContainer::getInstance()->set('help', new Help());
        CommandContainer::getInstance()->set('auth', new Auth());
        CommandContainer::getInstance()->set('server', new Server());
    }
}