<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/24
 * Time: 下午1:21
 */

namespace EasySwoole\Core\Component\Rpc\Client;


use EasySwoole\Core\Component\Rpc\Common\ServiceCaller;

class TaskObj extends ServiceCaller
{
    protected $successCall = null;
    protected $failCall = null;

    /**
     * @return null
     */
    public function getSuccessCall()
    {
        return $this->successCall;
    }


    public function setSuccessCall($successCall): TaskObj
    {
        $this->successCall = $successCall;
        return $this;
    }

    /**
     * @return null
     */
    public function getFailCall()
    {
        return $this->failCall;
    }

    public function setFailCall($failCall): TaskObj
    {
        $this->failCall = $failCall;
        return $this;
    }

}