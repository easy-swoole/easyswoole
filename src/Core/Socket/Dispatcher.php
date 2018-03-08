<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/25
 * Time: 下午12:07
 */

namespace EasySwoole\Core\Socket;

use EasySwoole\Core\Component\Invoker;
use EasySwoole\Core\Component\Spl\SplStream;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Socket\AbstractInterface\ExceptionHandler;
use EasySwoole\Core\Socket\Client\Tcp;
use EasySwoole\Core\Socket\Client\Udp;
use EasySwoole\Core\Socket\Client\WebSocket;
use EasySwoole\Core\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Core\Socket\Common\CommandBean;

class Dispatcher
{
    const TCP = 1;
    const WEB_SOCK = 2;
    const UDP = 3;
    const PACKAGE_PARSER_ERROR = 'PACKAGE_PARSER_ERROR';
    const TARGET_CONTROLLER_NOT_FOUND = 'TARGET_CONTROLLER_NOT_FOUND';
    private $parser;
    private $exceptionHandler;
    private $errorHandler = null;

    function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function setExceptionHandler(ExceptionHandler $handler = null):Dispatcher
    {
        $this->exceptionHandler = $handler;
        return $this;
    }

    public function onError(callable $callable = null)
    {
        $this->errorHandler = $callable;
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
                Trigger::error('dispatcher type error',__FILE__,__LINE__);
                return;
            }
        }
        $command = $this->parser->decode($data,$client);
        if(is_object($command)){
            $commandCopy = clone $command;
        }else{
            $commandCopy = $command;
        }
        if($command === null){
            if(is_callable($this->errorHandler)){
                try{
                    $ret = Invoker::callUserFunc($this->errorHandler,self::PACKAGE_PARSER_ERROR,$data,$client);
                    if($ret !== null){
                        $res = $this->parser->encode($ret,$client,$commandCopy);
                        if($res !== null){
                            Response::response($client,$res);
                        }
                    }
                }catch (\Throwable $exception){
                    trigger_error($exception->getTraceAsString());
                    Response::response($client,$exception->getTraceAsString());
                }
            }
            return;
        }else if($command instanceof CommandBean){
            $controller = $command->getControllerClass();
            if(class_exists($controller)){
                $response = new SplStream();
                try{
                    (new $controller($client,$command,$response));
                }catch (\Throwable $throwable){
                    if($this->exceptionHandler instanceof ExceptionHandler){
                        $data = $this->exceptionHandler->handler($throwable,$client,$command);
                        if($data !== null){
                            $response->write($data);
                        }
                    }else{
                        trigger_error($throwable->getTraceAsString());
                        $response->write($throwable->getMessage().$throwable->getTraceAsString());
                    }
                }
                $res = $this->parser->encode($response,$client,$commandCopy);
                if($res !== null){
                    Response::response($client,$res);
                }
            }else{
                if(is_callable($this->errorHandler)){
                    try{
                        $ret = Invoker::callUserFunc($this->errorHandler,self::TARGET_CONTROLLER_NOT_FOUND,$data,$client);
                        if($ret !== null){
                            $res = $this->parser->encode($ret,$client,$commandCopy);
                            if($res !== null){
                                Response::response($client,$res);
                            }
                        }
                    }catch (\Throwable $exception){
                        trigger_error($exception->getTraceAsString());
                        Response::response($client,$exception->getTraceAsString());
                    }
                }
            }
        }else{
            $res = $this->parser->encode($command,$client,$commandCopy);
            if($res !== null){
                Response::response($client,$res);
            }
        }
    }
}