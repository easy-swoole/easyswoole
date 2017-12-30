<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/29
 * Time: 下午1:31
 */

namespace EasySwoole\Core\Cluster\NetWork;


class Udp
{
    static function broadcast(string $str,int $port,$address = '255.255.255.255'):void
    {
        if(!($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new \Exception("Couldn't create socket: [$errorcode] $errormsg ");
        }
        socket_set_option($sock,65535,SO_BROADCAST,1);
        socket_sendto($sock,$str,strlen($str),0,$address,$port);
        socket_close($sock);
    }

    static function sendTo(string $str,int $port,$address):bool
    {
        if(!($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new \Exception("Couldn't create socket: [$errorcode] $errormsg ");
        }
        $bool =  (bool)socket_sendto($sock, $str , strlen($str) , 0 , $address , $port);
        socket_close($sock);
        return $bool;
    }

    static function listen(int $port,string $address = '0.0.0.0')
    {
        if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new \Exception("Couldn't create socket: [$errorcode] $errormsg ");
        }
        if( !socket_bind($sock, $address , $port) )
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new \Exception("Couldn't bind socket: [$errorcode] $errormsg ");
        }
        return $sock;
    }
}