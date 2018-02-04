<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/24
 * Time: 下午1:21
 */

namespace EasySwoole\Core\Component\Rpc\Client;


class TaskObj
{
    protected $serviceName;
    protected $serviceAction;
    protected $args = [];
    protected $successCall = null;
    protected $failCall = null;
    protected $serviceId = '';

    /**
     * @return mixed
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getServiceAction()
    {
        return $this->serviceAction;
    }

    public function setServiceAction($serviceAction)
    {
        $this->serviceAction = $serviceAction;
        return $this;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    public function setArgs(array $args)
    {
        $this->args = $args;
        return $this;
    }

    public function setArg($key,$item)
    {
        $this->args[$key] = $item;
        return $this;
    }

    public function getArg($key)
    {
        if(isset($this->args[$key])){
            return $this->args[$key];
        }else{
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    /**
     * @return null
     */
    public function getSuccessCall()
    {
        return $this->successCall;
    }


    public function setSuccessCall(callable $successCall)
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

    public function setFailCall(callable $failCall)
    {
        $this->failCall = $failCall;
        return $this;
    }

}