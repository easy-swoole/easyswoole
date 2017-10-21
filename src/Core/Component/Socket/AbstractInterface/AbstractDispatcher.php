<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/21
 * Time: 下午5:38
 */

namespace Core\Component\Socket\AbstractInterface;


use Core\Component\Socket\Client\TcpClient;
use Core\Component\Socket\Client\UdpClient;
use Core\Component\Socket\Common\Command;
use Core\Component\Socket\Common\ParserContainer;
use Core\Component\Socket\Common\CommandList;
use Core\Component\Socket\Response;
use Core\Swoole\Server;

abstract class AbstractDispatcher
{
    private static $instance;
    private $commandList;
    private $parserContainer;
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    function __construct()
    {
        $this->commandList = new CommandList();
        $this->parserContainer = new ParserContainer();
        $this->parserRegister($this->parserContainer);
        $this->commandRegister($this->commandList);
    }

    abstract protected function parserRegister(ParserContainer $container);
    abstract protected function commandRegister(CommandList $commandList);


    function dispatchTCP($fd,$reactorId,$data){
        $client = new TcpClient(Server::getInstance()->getServer()->connection_info($fd));
        $client->setReactorId($reactorId);
        $client->setFd($fd);
        $this->run($client,$data);
    }

    function dispatchUDP($data,$clientInfo){
        $client = new UdpClient($clientInfo);
        $this->run($client,$data);
    }

    function dispatchWEBSOCK(\swoole_websocket_frame $frame){
        $client = new TcpClient(Server::getInstance()->getServer()->connection_info($frame->fd));
        $client->setFd($frame->fd);
        $this->run($client,$frame->data);
    }

    private function run(AbstractClient $client,$data){
        $command = $this->parserContainer->getParserObj()->parser($client,$data)->getResultCommand();
        if($command instanceof Command){
            $handler = $this->commandList->getHandler($command);
            if(is_callable($handler)){
                try{
                    $ret = call_user_func_array($handler,array(
                       $command,$client
                    ));
                    if($ret !== null && !is_object($ret)){
                        Response::response($client,$ret);
                    }
                }catch (\Exception $exception){
                    trigger_error($exception->getTraceAsString());
                }
            }
        }
    }

}