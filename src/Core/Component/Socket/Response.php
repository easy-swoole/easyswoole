<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/10
 * Time: 下午10:20
 */

namespace Core\Component\Socket;


use Core\Component\Socket\Client\Client;
use Core\Component\Socket\Client\TcpClient;
use Core\Component\Socket\Client\UdpClient;
use Core\Swoole\Server;

class Response
{
    static function response(Client $client,$data){
        if($client instanceof TcpClient){
            if($client->getClientType() == Type::WEB_SOCKET){
                return Server::getInstance()->getServer()->push($client->getFd(),$data);
            }else{
                return Server::getInstance()->getServer()->send($client->getFd(),$data,$client->getReactorId());
            }
        }else if($client instanceof UdpClient){
             return Server::getInstance()->getServer()->sendto($client->getAddress(),$client->getPort(),$data);
        }else{
            trigger_error( "client is not validate");
            return false;
        }
    }
}