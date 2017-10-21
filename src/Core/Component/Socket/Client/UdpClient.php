<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/10
 * Time: 下午1:45
 */

namespace Core\Component\Socket\Client;


use Core\Component\Socket\AbstractInterface\AbstractClient;
use Core\Component\Socket\Type;

class UdpClient extends AbstractClient
{
    protected $server_socket;
    protected $server_port;
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
    public function getServerPort()
    {
        return $this->server_port;
    }

    /**
     * @param mixed $server_port
     */
    public function setServerPort($server_port)
    {
        $this->server_port = $server_port;
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

    function initialize()
    {
        $this->clientType = Type::UDP;
    }
}