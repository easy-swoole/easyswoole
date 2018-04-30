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
    public static function toNode(MessageBean $message,NodeBean $node)
    {

    }

    public static function toAllNode(MessageBean $message)
    {

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