<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/30
 * Time: 上午11:14
 */

namespace EasySwoole\Core\Component\Cluster\NetWork;


use EasySwoole\Core\Component\Cluster\Cluster;
use EasySwoole\Core\Component\Cluster\Common\MessageBean;
use EasySwoole\Core\Component\Cluster\Common\NodeBean;
use \EasySwoole\Core\Socket\Client\Udp;

class PacketParser
{
    static function pack(MessageBean $bean):string
    {
        $node = clone Cluster::getInstance()->currentNode();
        //去除敏感信息再发送
        $node->setBroadcastAddress([]);
        $node->setListenAddress([]);
        $node->setToken(null);
        $bean->setFromNode($node);
        return $bean->__toString();
    }

    static function unpack(string $jsonStr,Udp $udpClient):?MessageBean
    {
        $jsonArr = json_decode($jsonStr,true);
        if(is_array($jsonArr)){
            $message = new MessageBean($jsonArr);
            $message->setTime(time());
            $message->getFromNode()->setUdpInfo($udpClient);
            return $message;
        }else{
            return null;
        }
    }
}