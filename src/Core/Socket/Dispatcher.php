<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/25
 * Time: 下午12:07
 */

namespace EasySwoole\Core\Socket;


use EasySwoole\Core\Socket\AbstractInterface\Controller;
use EasySwoole\Core\Socket\AbstractInterface\ExceptionHandler;
use EasySwoole\Core\Socket\Client\Tcp;
use EasySwoole\Core\Socket\Client\Udp;
use EasySwoole\Core\Socket\Client\WebSocket;
use EasySwoole\Core\Socket\AbstractInterface\ParserInterface;

class Dispatcher
{
    const TCP = 1;
    const WEB_SOCK = 2;
    const UDP = 3;
    private $parser;
    private $exceptionHandler;

    function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function setExceptionHandler(ExceptionHandler $handler = null):Dispatcher
    {
        $this->exceptionHandler = $handler;
        return $this;
    }

    /*
     * $args:
     *  Tcp  $fd，$reactorId
     *  Web Socket swoole_websocket_frame $frame
     *  Udp array $client_info;
     */
    function dispatch($type ,string $data, ...$args):void
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
        $command = $this->parser->decode($data,$args);
        if($command == null){
            return;
        }
        $controller = $command->getControllerClass();
        if(!empty($controller)){
            if(class_exists($controller)){
                $ref = new \ReflectionClass($controller);
                $controller = $ref->newInstanceArgs(array(
                    $client,$command->getArgs()
                ));
                if($controller instanceof Controller){
                    try{
                        $res = $controller->__hook($command->getAction());
                    }catch (\Throwable $throwable){
                        if($this->exceptionHandler instanceof ExceptionHandler){
                            $res = $this->exceptionHandler->handler($throwable,$client,$command);
                        }else{
                            throw $throwable;
                        }
                    }
                    $res = $this->parser->encode($res,$args);
                    if(strlen($res) != 0){
                        Response::response($client,$res);
                    }
                }
            }
        }
    }
}