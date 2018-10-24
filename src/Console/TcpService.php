<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/20
 * Time: 下午11:24
 */

namespace EasySwoole\EasySwoole\Console;


use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Swoole\Memory\TableManager;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Socket\Bean\Response;
use EasySwoole\Socket\Config;
use EasySwoole\Socket\Dispatcher;
use Swoole\Table;
use EasySwoole\EasySwoole\Config as GlobalConfig;

class TcpService
{
    function __construct(?array $config)
    {
        //创建swoole table 用于记录客户端连接
        TableManager::getInstance()->add('Console.Auth',[
            'isAuth'=>['type'=>Table::TYPE_INT,'size'=>1],
            'tryTimes'=>['type'=>Table::TYPE_INT,'size'=>1]
        ]);

        if($config['ENABLE']){
            $conf = new Config();
            $conf->setParser(new TcpParser());
            $conf->setType($conf::TCP);
            $conf->setOnExceptionHandler(function ($server,\Throwable $throwable,$raw,$client,Response $response){
                $response->setMessage($throwable->getMessage());
                $response->setArgs([
                    'raw'=>$raw,
                    'exception'=>$throwable->getTraceAsString()
                ]);
                $response->setStatus($response::STATUS_RESPONSE_AND_CLOSE);
            });

            $conf->setOnExceptionHandler(function ($server,\Throwable $throwable,$data,$client,Response $response){
                Trigger::getInstance()->throwable($throwable);
                $response->setMessage($throwable->getMessage());
                $response->setStatus($response::STATUS_RESPONSE_AND_CLOSE);
            });

            $dispatcher = new Dispatcher($conf);
            $sub = ServerManager::getInstance()->addServer('ConsoleTcp',$config['PORT'],SWOOLE_TCP,$config['HOST'],[
                'heartbeat_check_interval'=>$config['EXPIRE'],
                'heartbeat_idle_time'=>$config['EXPIRE'],
                'open_length_check' => true,
                'package_length_type'   => 'N',
                'package_length_offset' => 0,
                'package_body_offset'   => 4,
                'package_max_length'    => 1024*1024
            ]);
            $sub->set($sub::onReceive,function (\swoole_server $server,$fd, $reactor_id, $data)use($dispatcher){
                $dispatcher->dispatch($server,$data,$fd,$reactor_id);
            });
            $sub->set($sub::onConnect,function (\swoole_server $server, int $fd, int $reactorId){
                $hello = 'Hello !'.GlobalConfig::getInstance()->getConf('SERVER_NAME');
                $server->send($fd,TcpParser::pack($hello),$reactorId);
                if(GlobalConfig::getInstance()->getConf('CONSOLE.AUTH')){
                    $server->send($fd,TcpParser::pack('please enter your auth key; auth $authKey'),$reactorId);
                }
            });
            $sub->set($sub::onClose,function (\swoole_server $server, int $fd, int $reactorId){
                TableManager::getInstance()->get('Console.Auth')->del($fd);
            });
        }
    }

    static function push(string $string)
    {
        $string = TcpParser::pack($string);
        $table = TableManager::getInstance()->get('Console.Auth');
        foreach ($table as $fd => $value){
            if(GlobalConfig::getInstance()->getConf('CONSOLE.AUTH') && $value['isAuth'] == 0){
                continue;
            }
            ServerManager::getInstance()->getSwooleServer()->send($fd,$string);
        }
    }
}