<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/24
 * Time: 下午1:28
 */

namespace EasySwoole\Core\Component\Rpc\Client;


use EasySwoole\Core\Component\Rpc\Server\ServiceNode;
use EasySwoole\Core\Component\Spl\SplBean;

class ResponseObj extends SplBean
{
    protected $action;
    protected $error;
    protected $result;
    protected $args = [];
    protected $status;
    protected $serviceNode = null;

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action): void
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error): void
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result): void
    {
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function getServiceNode():?ServiceNode
    {
        return $this->serviceNode;
    }

    public function setServiceNode(ServiceNode $serviceNode): void
    {
        $this->serviceNode = $serviceNode;
    }

}