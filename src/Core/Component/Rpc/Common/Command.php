<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/1
 * Time: 下午4:34
 */

namespace EasySwoole\Core\Component\Rpc\Common;


use EasySwoole\Core\Socket\Common\CommandBean;

class Command extends CommandBean
{
    protected $signature;

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

    protected function initialize(): void
    {
        if(empty($this->time)){
            $this->time = time();
        }
    }

}