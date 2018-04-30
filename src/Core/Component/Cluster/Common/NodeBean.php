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
use EasySwoole\Core\Socket\Client\Udp;
use EasySwoole\Core\Utility\Random;

class NodeBean extends SplBean
{
    protected $enable;
    protected $token;
    protected $broadcastAddress = [];
    protected $listenAddress = '0.0.0.0';
    protected $listenPort;
    protected $broadcastTTL;
    protected $nodeTimeout;
    protected $nodeName;
    protected $nodeId;
    protected $udpInfo;
    protected $lastBeatBeatTime;

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

    public function getListenPort()
    {
        return $this->listenPort;
    }

    public function setListenPort( $listenPort): void
    {
        $this->listenPort = $listenPort;
    }

    /**
     * @return string
     */
    public function getListenAddress(): string
    {
        return $this->listenAddress;
    }

    /**
     * @param string $listenAddress
     */
    public function setListenAddress(string $listenAddress): void
    {
        $this->listenAddress = $listenAddress;
    }


    /**
     * @return mixed
     */
    public function getBroadcastTTL():int
    {
        return intval($this->broadcastTTL);
    }

    /**
     * @param mixed $broadcastTTL
     */
    public function setBroadcastTTL(int $broadcastTTL): void
    {
        if($broadcastTTL < 1){
            $broadcastTTL = 5;
        }
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
     * @return mixed
     */
    public function getUdpInfo():?Udp
    {
        return $this->udpInfo;
    }

    /**
     * @param mixed $udpInfo
     */
    public function setUdpInfo($udpInfo): void
    {
        $this->udpInfo = $udpInfo;
    }

    /**
     * @param mixed $nodeId
     */
    public function setNodeId($nodeId): void
    {
        $this->nodeId = $nodeId;
    }

    /**
     * @return mixed
     */
    public function getLastBeatBeatTime()
    {
        return $this->lastBeatBeatTime;
    }

    /**
     * @param mixed $lastBeatBeatTime
     */
    public function setLastBeatBeatTime($lastBeatBeatTime): void
    {
        $this->lastBeatBeatTime = $lastBeatBeatTime;
    }


    protected function initialize(): void
    {
        if(empty($this->nodeId)){
            $this->nodeId = Random::randStr(8);
        }
        if($this->getBroadcastTTL() < 1){
            $this->setBroadcastTTL(5);
        }
        if(is_array($this->udpInfo)){
            $this->udpInfo = new Udp($this->udpInfo);
        }else{
            $this->udpInfo = null;
        }
    }
}