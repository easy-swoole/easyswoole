<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/24
 * Time: 下午10:44
 */

namespace EasySwoole\Core\Component\Cluster\Bean;


use EasySwoole\Core\Component\Spl\SplBean;

class Node extends SplBean
{
    protected $listenAddress;
    protected $listenPort;
    protected $broadcastTTL;
    protected $serviceTTL;
    protected $nodeName;
    protected $nodeId;

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