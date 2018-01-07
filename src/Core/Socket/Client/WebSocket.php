<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/25
 * Time: 下午2:08
 */

namespace EasySwoole\Core\Socket\Client;


class WebSocket
{
    private $fd;
    private $data;
    private $opCode;
    private $isFinish;

    function __construct(\swoole_websocket_frame $frame = null)
    {
        if($frame){
            $this->fd = $frame->fd;
            $this->data = $frame->data;
            $this->opCode = $frame->opcode;
            $this->isFinish = $frame->finish;
        }
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
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getOpCode()
    {
        return $this->opCode;
    }

    /**
     * @param mixed $opCode
     */
    public function setOpCode($opCode)
    {
        $this->opCode = $opCode;
    }

    /**
     * @return mixed
     */
    public function getisFinish()
    {
        return $this->isFinish;
    }

    /**
     * @param mixed $isFinish
     */
    public function setIsFinish($isFinish)
    {
        $this->isFinish = $isFinish;
    }



}