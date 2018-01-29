<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/28
 * Time: 下午10:53
 */

namespace EasySwoole\Core\Component\Cluster;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Spl\SplArray;

class Config extends SplArray
{
    use Singleton;
    protected $enable;
    protected $token;
    protected $broadcastAddress;
    protected $listenAddress;
    protected $listenPort;
    protected $broadcastTTL;
    protected $serviceTTL;
    protected $nodeName;
    protected $nodeId;

    function __construct($input = array(), int $flags = 0, string $iterator_class = "ArrayIterator")
    {
        $conf = \EasySwoole\Config::getInstance()->getConf('CLUSTER');
        parent::__construct($conf, $flags, $iterator_class);
    }

    /**
     * @return mixed
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     * @param mixed $enable
     */
    public function setEnable($enable): void
    {
        $this->enable = $enable;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getBroadcastAddress()
    {
        return $this->broadcastAddress;
    }

    /**
     * @param mixed $broadcastAddress
     */
    public function setBroadcastAddress($broadcastAddress): void
    {
        $this->broadcastAddress = $broadcastAddress;
    }

    /**
     * @return mixed
     */
    public function getListenAddress()
    {
        return $this->listenAddress;
    }

    /**
     * @param mixed $listenAddress
     */
    public function setListenAddress($listenAddress): void
    {
        $this->listenAddress = $listenAddress;
    }

    /**
     * @return mixed
     */
    public function getListenPort()
    {
        return $this->listenPort;
    }

    /**
     * @param mixed $listenPort
     */
    public function setListenPort($listenPort): void
    {
        $this->listenPort = $listenPort;
    }

    /**
     * @return mixed
     */
    public function getBroadcastTTL()
    {
        return $this->broadcastTTL;
    }

    /**
     * @param mixed $broadcastTTL
     */
    public function setBroadcastTTL($broadcastTTL): void
    {
        $this->broadcastTTL = $broadcastTTL;
    }

    /**
     * @return mixed
     */
    public function getServiceTTL()
    {
        return $this->serviceTTL;
    }

    /**
     * @param mixed $serviceTTL
     */
    public function setServiceTTL($serviceTTL): void
    {
        $this->serviceTTL = $serviceTTL;
    }

    /**
     * @return mixed
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }

    /**
     * @param mixed $nodeName
     */
    public function setNodeName($nodeName): void
    {
        $this->nodeName = $nodeName;
    }

    /**
     * @return mixed
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * @param mixed $nodeId
     */
    public function setNodeId($nodeId): void
    {
        $this->nodeId = $nodeId;
    }

}