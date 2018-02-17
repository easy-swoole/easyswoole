<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/28
 * Time: 下午10:53
 */

namespace EasySwoole\Core\Component\Cluster;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Spl\SplBean;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Utility\Random;

class Config extends SplBean
{
    use Singleton;
    protected $enable;
    protected $token;
    protected $broadcastAddress;
    protected $listenAddress;
    protected $listenPort;
    protected $broadcastTTL;
    protected $serviceTTL;
    protected $serverName;
    protected $serverId;

    function __construct()
    {
        $conf = \EasySwoole\Config::getInstance()->getConf('CLUSTER');
        parent::__construct($conf);
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
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * @param mixed $serverName
     */
    public function setServerName($serverName): void
    {
        $this->serverName = $serverName;
    }

    /**
     * @return mixed
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * @param mixed $serverId
     */
    public function setServerId($serverId): void
    {
        $this->serverId = $serverId;
    }

    protected function initialize(): void
    {
        if(empty($this->serverId)){
            if($this->enable && empty($this->token)){
                Trigger::throwable(new \Exception('cluster config token could not be empty and set cluster mode disable automatic'));
                $this->enable = false;
            }
            $this->serverId = Random::randStr(8);
        }
    }
}