<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/20
 * Time: 下午4:29
 */

namespace EasySwoole\Core\Socket\Client;


use EasySwoole\Core\Component\Spl\SplBean;


/*
 * 在onPacket 回调中client_info与Udp Client一致。
 */


class Udp extends SplBean
{
    protected $server_socket = -1;
    protected $address;
    protected $port;

    /**
     * @return mixed
     */
    public function getServerSocket()
    {
        return $this->server_socket;
    }

    /**
     * @param mixed $server_socket
     */
    public function setServerSocket($server_socket)
    {
        $this->server_socket = $server_socket;
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
}