<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/25
 * Time: 下午12:07
 */

namespace EasySwoole\Core\Socket;


use EasySwoole\Core\Socket\Client\Tcp;
use EasySwoole\Core\Socket\Client\Udp;
use EasySwoole\Core\Socket\Client\WebSocket;
use EasySwoole\Core\Socket\Command\ParserInterface;

class Dispatch
{
    const TCP = 1;
    const WEB_SOCK = 2;
    const UDP = 3;
    private $parser;
    function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /*
     * Tcp  $fd，$reactorId
     * Web Socket swoole_websocket_frame $frame
     * Udp array $client_info;
     */
    function dispatch($type ,string $data, ...$args)
    {
        switch ($type){
            case self::TCP:{
                $client = new Tcp($args[0],$args[1]);
                break;
            }
            case self::WEB_SOCK:{
                $client = new WebSocket($args[0]);
                break;
            }
            case self::UDP:{
                $client = new Udp($args[0]);
                break;
            }
            default:{
                throw new \Exception('dispatch type error');
            }
        }
        $command = $this->parser->decode($data);
        $controller = $command->getControllerClass();
        if(!empty($controller)){
            if(class_exists($controller)){
                $controller = new $controller($client,$command->getArgs());
                if($controller instanceof AbstractController){
                    $controller->__hook($command->getAction());
                    $res = $controller->getResponse()->__toString();
                    if(!empty($res)){
                        $res = $this->parser->encode($res);
                        if(!isset($res)){

                        }
                    }
                }
            }

        }

    }
}