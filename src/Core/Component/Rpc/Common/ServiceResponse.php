<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/26
 * Time: 下午12:39
 */

namespace EasySwoole\Core\Component\Rpc\Common;


class ServiceResponse extends ServiceCaller
{
    protected $status = Status::OK;

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    function setResult($data){
        $this->setArgs($data);
    }

    function getResult()
    {
        return $this->getArgs();
    }


}