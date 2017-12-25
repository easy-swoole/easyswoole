<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/25
 * Time: 下午3:10
 */

namespace EasySwoole\Core\Socket;


use EasySwoole\Core\Socket\Client\Tcp;
use EasySwoole\Core\Socket\Client\Udp;
use EasySwoole\Core\Socket\Client\WebSocket;
use EasySwoole\Core\Swoole\ServerManager;

class Response
{
    /*
     *  int $opcode = 1, bool $finish = true在给websocket客户端回复时候有效
     */
    static function response($client,$data, int $opCode = 1, bool $finish = true)
    {
        $server = ServerManager::getInstance()->getServer();
        if($client instanceof Tcp){
            $server->send($client->getFd(),$data,$client->getReactorId());
        }else if($client instanceof WebSocket){
            return $server->push($client->getFd(),$data,$opCode,$finish);
        }else if($client instanceof Udp){
            return $server->sendto($client->getAddress(),$client->getPort(),$data,$client->getServerSocket());
        }else{
            return false;
        }
    }
}