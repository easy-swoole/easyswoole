<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/31
 * Time: 下午10:07
 */

namespace EasySwoole\Core\Component\Cluster\NetWork;


class Udp
{
    static function broadcast(string $str,int $port,$address = '127.0.0.1'):void
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
        $error = $errMsg = null;
        $stream = stream_socket_server(
            "udp://{$address}:{$port}",
            $error,
            $errMsg,
            STREAM_SERVER_BIND
        );
        if($errMsg){
            throw new \Exception("cluster server bind error on msg :{$errMsg}");
        }
        return $stream;
    }
}