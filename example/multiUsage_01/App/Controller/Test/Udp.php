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
     * 当前有bug，在Server启动前监听udp，在Event下，相同client在cli模式下，可以获得服务端返回数据，但在
     * swoole http server onrequest下失效
     *  cli下udp客户端测试代码  echo \App\Model\UdpClient::sendTo("127.0.0.1",9502,'as');
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