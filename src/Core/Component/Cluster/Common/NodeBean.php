<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/8
 * Time: 下午4:05
 */

namespace EasySwoole\Core\Component\Cluster\Common;


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