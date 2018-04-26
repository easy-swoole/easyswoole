<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/26
 * Time: 下午12:15
 */

namespace EasySwoole\Core\Component\Rpc\Common;


use EasySwoole\Core\Component\Spl\SplBean;

class ServiceCaller extends SplBean
{
    protected $serviceName;
    protected $serviceGroup;
    protected $serviceAction;
    protected $args = null;

    /**
     * @return mixed
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @param mixed $serviceName
     */
    public function setServiceName($serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @return mixed
     */
    public function getServiceGroup()
    {
        return $this->serviceGroup;
    }

    /**
     * @param mixed $serviceGroup
     */
    public function setServiceGroup($serviceGroup): void
    {
        $this->serviceGroup = $serviceGroup;
    }

    /**
     * @return mixed
     */
    public function getServiceAction()
    {
        return $this->serviceAction;
    }

    /**
     * @param mixed $serviceAction
     */
    public function setServiceAction($serviceAction): void
    {
        $this->serviceAction = $serviceAction;
        $this->initialize();
    }

    /**
     * @return null
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param null $args
     */
    public function setArgs($args): void
    {
        $this->args = $args;
    }

    protected function initialize(): void
    {
        if(empty($this->serviceAction)){
            $this->serviceAction = 'index';
        }
    }

}