<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/11
 * Time: 下午12:19
 */

namespace App\Model;


class UdpClient
{
    static function sendTo($ip,$port,$data,$timeout = 1){
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($sock,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>$timeout,"usec"=>0));
        socket_sendto($sock, $data, strlen($data), 0, $ip, $port);
        socket_recvfrom($sock, $buf, 65533, 0, $ip, $port);
        socket_close($sock);
        return $buf;
    }

}