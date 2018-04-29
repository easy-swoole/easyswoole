<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/27
 * Time: 下午4:33
 */

namespace EasySwoole\Core\Component\Cluster\Common;


use EasySwoole\Core\Component\Spl\SplBean;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Utility\Random;

class NodeBean extends SplBean
{
    protected $enable;
    protected $token;
    protected $broadcastAddress = [];
    protected $listenAddress = [];
    protected $broadcastTTL;
    protected $nodeTimeout;
    protected $nodeName;
    protected $nodeId;

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
     * @return array
     */
    public function getBroadcastAddress(): array
    {
        return $this->broadcastAddress;
    }

    /**
     * @param array $broadcastAddress
     */
    public function setBroadcastAddress(array $broadcastAddress): void
    {
        $this->broadcastAddress = $broadcastAddress;
    }

    /**
     * @return array
     */
    public function getListenAddress(): array
    {
        return $this->listenAddress;
    }

    /**
     * @param array $listenAddress
     */
    public function setListenAddress(array $listenAddress): void
    {
        $this->listenAddress = $listenAddress;
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
    public function getNodeTimeout()
    {
        return $this->nodeTimeout;
    }

    /**
     * @param mixed $nodeTimeout
     */
    public function setNodeTimeout($nodeTimeout): void
    {
        $this->nodeTimeout = $nodeTimeout;
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

    protected function initialize(): void
    {
        if(empty($this->nodeIdId)){
            $this->nodeId = Random::randStr(8);
        }
        if($this->enable && empty($this->token)){
            Trigger::throwable(new \Exception('cluster token could not be empty and set cluster mode disable automatic'));
            $this->enable = false;
        }
        if($this->enable && empty($this->listenAddress)){
            Trigger::throwable(new \Exception('cluster listenAddress could not be empty and set cluster mode disable automatic'));
            $this->enable = false;
        }
    }
}