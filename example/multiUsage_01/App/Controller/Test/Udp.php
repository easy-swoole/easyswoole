<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/11
 * Time: 下午12:17
 */

namespace App\Controller\Test;


use App\Model\UdpClient;
use Core\Component\Logger;

class Udp extends AbstractController
{
    /*
     * /test/udp/multiProtocol/
     */
    function multiProtocol(){
        $data = UdpClient::sendTo('127.0.0.1','9502',"i am client");
        Logger::console("receive from udp server data@{$data}");
        $this->response()->write("udp server say {$data} as 9502");
    }
    function eventLoop(){
        $data = UdpClient::sendTo('127.0.0.1','9503',"i am client");
        Logger::console("receive from udp server data@{$data}");
        $this->response()->write("udp server say {$data} as 9503");
    }
}