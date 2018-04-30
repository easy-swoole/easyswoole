<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/30
 * Time: 上午11:46
 */

namespace EasySwoole\Core\Component\Cluster\NetWork;


use EasySwoole\Core\Component\Cluster\Cluster;
use EasySwoole\Core\Component\Cluster\Common\MessageBean;
use EasySwoole\Core\Component\Cluster\Common\NodeBean;

class Deliverer
{
    /*
     * 调用此方法，请确保知晓节点的udp信息
     */
    public static function toNode(MessageBean $message,NodeBean $node)
    {
        $message = PacketParser::pack($message);
        Udp::sendTo($message,$node->getUdpInfo()->getPort(),$node->getUdpInfo()->getAddress());
    }


    public static function toAllNode(MessageBean $message)
    {
        $message = PacketParser::pack($message);
        $nodes = Cluster::getInstance()->allNodes();
        foreach ($nodes as $node){
            Udp::sendTo($message,$node->getUdpInfo()->getPort(),$node->getUdpInfo()->getAddress());
        }
    }

    public static function broadcast(MessageBean $message)
    {
        $message = PacketParser::pack($message);
        $addresses = Cluster::getInstance()->currentNode()->getBroadcastAddress();
        foreach ($addresses as $item){
            $item = explode(':',$item);
            Udp::broadcast($message,$item[1],$item[0]);
        }
    }
}