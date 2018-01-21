<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午3:22
 */

namespace EasySwoole\Core\Component\Rpc;


use EasySwoole\Core\Component\Spl\SplBean;
use EasySwoole\Core\Utility\Random;

class ServiceNode extends SplBean
{
    protected $serviceId;
    protected $serverName;
    protected $address;
    protected $port;
    protected $lastHeartBeat;

    /**
     * @return mixed
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param mixed $serviceId
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
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
    public function setServerName($serverName)
    {
        $this->serverName = $serverName;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return mixed
     */
    public function getLastHeartBeat()
    {
        return $this->lastHeartBeat;
    }

    /**
     * @param mixed $lastHeartBeat
     */
    public function setLastHeartBeat($lastHeartBeat)
    {
        $this->lastHeartBeat = $lastHeartBeat;
    }

    protected function initialize(): void
    {
        if(empty($this->serviceId)){
            $this->serviceId = md5(Random::randStr(8));
        }
    }
}