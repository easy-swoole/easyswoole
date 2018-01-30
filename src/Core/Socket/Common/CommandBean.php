<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/22
 * Time: 下午1:18
 */

namespace EasySwoole\Core\Socket\Common;
use EasySwoole\Core\Component\Spl\SplBean;

class CommandBean extends SplBean
{
    protected $controllerClass = null;
    protected $action = 'index';
    protected $error;
    protected $result;
    protected $args = [];
    protected $status;

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


    public function getControllerClass():?string
    {
        return $this->controllerClass;
    }

    /**
     * @param string $controllerClass
     */
    public function setControllerClass(?string $controllerClass)
    {
        $this->controllerClass = $controllerClass;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action)
    {
        $this->action = $action;
    }

    public function getArg($key)
    {
        if(isset($this->args[$key])){
            return $this->args[$key];
        }else{
            return null;
        }
    }

    public function getArgs():array
    {
        return $this->args;
    }


    public function addArg($key,$val)
    {
        $this->args[$key] = $val;
    }

    public function setArgs(array $data)
    {
        $this->args = $data;
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
    public function setError($error)
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
    public function setResult($result)
    {
        $this->result = $result;
    }


}