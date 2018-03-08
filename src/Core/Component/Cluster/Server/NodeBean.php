<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/8
 * Time: 下午12:03
 */

namespace EasySwoole\Core\Component\Cluster\Server;


use EasySwoole\Core\Component\Spl\SplBean;

class NodeBean extends SplBean
{
    protected $broadcastTTL;
    protected $serviceTTL;
    protected $serverName;
    protected $serverId;
    protected $udpAddress;
    protected $udpPort;
}