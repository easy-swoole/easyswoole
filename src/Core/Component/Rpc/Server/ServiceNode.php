<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/21
 * Time: 下午3:22
 */

namespace EasySwoole\Core\Component\Rpc\Server;


use EasySwoole\Core\Component\Cluster\Config;
use EasySwoole\Core\Component\Spl\SplBean;

class ServiceNode extends SplBean
{
    protected $serverId;
    protected $serviceName;
    protected $address = '127.0.0.1';
    protected $port;
    protected $lastHeartBeat;
    protected $encrypt = false;
    protected $token = null;

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
    public function setServerId($serverId)
    {
        $this->serverId = $serverId;
    }

    /**
     * @return mixed
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @param mixed $serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
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

    /**
     * @return string
     */
    public function getEncrypt(): string
    {
        return $this->encrypt;
    }

    /**
     * @param string $encrypt
     */
    public function setEncrypt(string $encrypt): void
    {
        $this->encrypt = $encrypt;
    }

    /**
     * @return null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param null $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    protected function initialize(): void
    {
        if(empty($this->serverId)){
            $this->serverId = Config::getInstance()->getServerId();
            //在swoole table 中以string存储
            if($this->encrypt == 'false'){
                $this->encrypt = false;
            }
        }
    }
}