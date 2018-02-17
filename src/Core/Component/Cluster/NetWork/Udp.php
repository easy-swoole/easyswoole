<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/31
 * Time: 下午10:07
 */

namespace EasySwoole\Core\Component\Cluster\NetWork;


use EasySwoole\Core\Component\Trigger;

class Udp
{
    static function broadcast(string $str,int $port,$address = '127.0.0.1'):void
    {
        if(!($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            Trigger::error("Couldn't create socket: [{$errorcode}] {$errormsg} ",__FILE__,__LINE__);
        }else{
            socket_set_option($sock,65535,SO_BROADCAST,true);
            socket_sendto($sock,$str,strlen($str),0,$address,$port);
            socket_close($sock);
        }
    }

    static function sendTo(string $str,int $port,$address):bool
    {
        if(!($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            Trigger::error("Couldn't create socket: [{$errorcode}] {$errormsg} ",__FILE__,__LINE__);
            return false;
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
            Trigger::error("cluster server bind error on msg :{$errMsg}",__FILE__,__LINE__);
        }
        return $stream;
    }
}