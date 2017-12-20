<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/20
 * Time: 下午4:06
 */

namespace EasySwoole\Core\Socket\Client;


class Tcp
{
    private $reactorId;
    private $fd;
    private $isWebSocket = false;

    final function __construct($fd = null,$reactorId = null)
    {
        $this->fd = $fd;
        $this->reactorId = $reactorId;
    }

    /**
     * @return mixed
     */
    public function getReactorId()
    {
        return $this->reactorId;
    }

    /**
     * @param mixed $reactorId
     */
    public function setReactorId($reactorId)
    {
        $this->reactorId = $reactorId;
    }

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
     * @return bool
     */
    public function isWebSocket(): bool
    {
        return $this->isWebSocket;
    }

    /**
     * @param bool $isWebSocket
     */
    public function setIsWebSocket(bool $isWebSocket)
    {
        $this->isWebSocket = $isWebSocket;
    }

}