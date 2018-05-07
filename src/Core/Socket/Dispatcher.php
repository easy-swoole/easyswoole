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
use EasySwoole\Core\Swoole\ServerManager;

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

    function __construct(string $parserInterface)
    {
        try{
            $ref = new \ReflectionClass($parserInterface);
            if($ref->implementsInterface(ParserInterface::class)){
                $this->parser = $parserInterface;
            }else{
                throw new \Exception("class {$parserInterface} not a implement ".'EasySwoole\Core\Socket\AbstractInterface\ParserInterface');
            }
        }catch (\Throwable $throwable){
            Trigger::throwable($throwable);
            throw $throwable;
        }
    }

    public function setExceptionHandler(string $handler = null):Dispatcher
    {
        if($handler == null){
            return $this;
        }
        try{
            $ref = new \ReflectionClass($handler);
            if($ref->implementsInterface(ExceptionHandler::class)){
                $this->exceptionHandler = $handler;
            }else{
                throw new \Exception("class {$handler} not a implement ".'EasySwoole\Core\Socket\AbstractInterface\ExceptionHandler');
            }
        }catch (\Throwable $throwable){
            Trigger::throwable($throwable);
            throw $throwable;
        }
        return $this;
    }

    public function setErrorHandler(callable $callable = null)
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
        $command = null;
        try{
            $command = $this->parser::decode($data,$client);
        }catch (\Throwable $throwable){
            Trigger::throwable($throwable);
        }
        //若解析器返回null，则调用errorHandler，且状态为包解析错误
        if($command === null){
            $this->hookError(self::PACKAGE_PARSER_ERROR,$data,$client);
            return;
        }else if($command instanceof CommandBean){
            //解包正确
            $controller = $command->getControllerClass();
            if(class_exists($controller)){
                try{
                    $response = new SplStream();
                    (new $controller($client,$command,$response));
                    $res = $this->parser::encode($response->__toString(),$client);
                    if($res !== null){
                        Response::response($client,$res);
                    }
                }catch (\Throwable $throwable){
                    $this->hookException($throwable,$data,$client);
                }
            }else{
                $this->hookError(self::TARGET_CONTROLLER_NOT_FOUND,$data,$client);
            }
        }
    }

    private function hookError($status,string $raw,$client)
    {
        if(is_callable($this->errorHandler)){
            try{
                $ret = Invoker::callUserFunc($this->errorHandler,$status,$raw,$client);
                if(is_string($ret)){
                    $res = $this->parser::encode($ret,$client);
                    if($res !== null){
                        Response::response($client,$res);
                    }
                }
            }catch (\Throwable $exception){
                $this->hookException($exception,$raw,$client);
            }
        }else{
            //默认没有错误处理的时候，关闭连接
            $this->closeClient($client);
        }
    }

    private function closeClient($client)
    {
        if(!$client instanceof Udp){
            ServerManager::getInstance()->getServer()->close($client->getFd());
        }
    }

    private function hookException(\Throwable $throwable,string $raw,$client)
    {
        if(class_exists($this->exceptionHandler)){
            Try{
                $ret = $this->exceptionHandler::handler($throwable,$raw,$client);
                if(is_string($ret)){
                    $res = $this->parser::encode($ret,$client);
                    if($res !== null){
                        Response::response($client,$res);
                    }
                }
            }catch (\Throwable $throwable){
                Trigger::throwable($throwable);
                $this->closeClient($client);
            }
        }else{
            $this->closeClient($client);
        }
    }
}