<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/20
 * Time: 下午11:24
 */

namespace EasySwoole\EasySwoole\Console;


use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Socket\Bean\Response;
use EasySwoole\Socket\Config;
use EasySwoole\Socket\Dispatcher;

class TcpService
{
    function __construct(?array $config)
    {
        if($config['enable']){
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
            $dispatcher = new Dispatcher($conf);
            $sub = ServerManager::getInstance()->addServer('ConsoleTcp',$config['port'],SWOOLE_TCP,$config['host'],[
                'open_length_check' => true,
                'package_length_type'   => 'N',
                'package_length_offset' => 0,
                'package_body_offset'   => 4,
                'package_max_length'    => 1024*1024
            ]);
            $sub->set($sub::onReceive,function (\swoole_server $server,$fd, $reactor_id, $data)use($dispatcher){
                $dispatcher->dispatch($server,$data,$fd,$reactor_id);
            });
        }
    }
}