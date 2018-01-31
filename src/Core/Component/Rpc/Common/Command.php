<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/31
 * Time: 下午6:40
 */

namespace EasySwoole\Core\Component\Rpc\Common;


use EasySwoole\Core\Socket\Common\CommandBean;

class Command extends CommandBean
{
    protected $time;
    protected $signature;

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time): void
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param mixed $signature
     */
    public function setSignature($signature): void
    {
        $this->signature = $signature;
    }

}