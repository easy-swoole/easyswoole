<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/25
 * Time: 上午11:47
 */

namespace EasySwoole\Core\Socket\Command;


use EasySwoole\Core\Component\Spl\SplBean;

class CommandBean extends SplBean
{
    protected $controllerClass = null;
    protected $action = 'index';
    protected $args = [];

    public function getControllerClass():?string
    {
        return $this->controllerClass;
    }

    /**
     * @param string $controllerClass
     */
    public function setControllerClass(string $controllerClass)
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
}