<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/17
 * Time: 下午1:06
 */

namespace EasySwoole\Core\Swoole\Process;


class Msg
{
    private $fromWorkerId = -1;
    private $workerId = -1;
    private $message = null;

    /**
     * @return int
     */
    public function getFromWorkerId(): int
    {
        return $this->fromWorkerId;
    }

    /**
     * @param int $fromWorkerId
     */
    public function setFromWorkerId(int $fromWorkerId)
    {
        $this->fromWorkerId = $fromWorkerId;
    }

    /**
     * @return int
     */
    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    /**
     * @param int $workerId
     */
    public function setWorkerId(int $workerId)
    {
        $this->workerId = $workerId;
    }

    /**
     * @return null
     */
    public function getMessage()
    {
        return \swoole_serialize::unpack($this->message);
    }

    /**
     * @param null $message
     */
    public function setMessage($message)
    {
        $this->message = \swoole_serialize::pack($this->message);
    }
}