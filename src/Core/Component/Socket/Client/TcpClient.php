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

class TcpClient extends AbstractClient
{
    protected $server_port;
    protected $server_fd;
    protected $socket_type;
    protected $remote_port;
    protected $remote_ip;
    protected $reactor_id;
    protected $connect_time;
    protected $last_time;
    protected $close_errno;
    protected $websocket_status;
    protected $uid;
    protected $ssl_client_cert;
    protected $fd;
    /**
     * @return mixed
     */
    public function getFd()
    {
        return $this->fd;
    }

    /**
     * @param mixed $fd
     */
    public function setFd($fd)
    {
        $this->fd = $fd;
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
    public function getServerFd()
    {
        return $this->server_fd;
    }

    /**
     * @param mixed $server_fd
     */
    public function setServerFd($server_fd)
    {
        $this->server_fd = $server_fd;
    }

    /**
     * @return mixed
     */
    public function getSocketType()
    {
        return $this->socket_type;
    }

    /**
     * @param mixed $socket_type
     */
    public function setSocketType($socket_type)
    {
        $this->socket_type = $socket_type;
    }

    /**
     * @return mixed
     */
    public function getRemotePort()
    {
        return $this->remote_port;
    }

    /**
     * @param mixed $remote_port
     */
    public function setRemotePort($remote_port)
    {
        $this->remote_port = $remote_port;
    }

    /**
     * @return mixed
     */
    public function getRemoteIp()
    {
        return $this->remote_ip;
    }

    /**
     * @param mixed $remote_ip
     */
    public function setRemoteIp($remote_ip)
    {
        $this->remote_ip = $remote_ip;
    }

    /**
     * @return mixed
     */
    public function getReactorId()
    {
        return $this->reactor_id;
    }

    /**
     * @param mixed $reactor_id
     */
    public function setReactorId($reactor_id)
    {
        $this->reactor_id = $reactor_id;
    }

    /**
     * @return mixed
     */
    public function getConnectTime()
    {
        return $this->connect_time;
    }

    /**
     * @param mixed $connect_time
     */
    public function setConnectTime($connect_time)
    {
        $this->connect_time = $connect_time;
    }

    /**
     * @return mixed
     */
    public function getLastTime()
    {
        return $this->last_time;
    }

    /**
     * @param mixed $last_time
     */
    public function setLastTime($last_time)
    {
        $this->last_time = $last_time;
    }

    /**
     * @return mixed
     */
    public function getCloseErrno()
    {
        return $this->close_errno;
    }

    /**
     * @param mixed $close_errno
     */
    public function setCloseErrno($close_errno)
    {
        $this->close_errno = $close_errno;
    }

    /**
     * @return mixed
     */
    public function getWebsocketStatus()
    {
        return $this->websocket_status;
    }

    /**
     * @param mixed $websocket_status
     */
    public function setWebsocketStatus($websocket_status)
    {
        $this->websocket_status = $websocket_status;
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param mixed $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return mixed
     */
    public function getSslClientCert()
    {
        return $this->ssl_client_cert;
    }

    /**
     * @param mixed $ssl_client_cert
     */
    public function setSslClientCert($ssl_client_cert)
    {
        $this->ssl_client_cert = $ssl_client_cert;
    }
    protected function initialize()
    {
        if($this->getWebsocketStatus()){
            $this->setClientType(Type::WEB_SOCKET);
        }else{
            $this->setClientType(Type::TCP);
        }
    }

}