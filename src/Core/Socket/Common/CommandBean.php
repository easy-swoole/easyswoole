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
    protected $args = [];

    /**
     * @return null
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * @param null $controllerClass
     */
    public function setControllerClass($controllerClass): void
    {
        $this->controllerClass = $controllerClass;
    }

    /**
     * @return string
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(?string $action): void
    {
        $this->action = $action;
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

    public function setArg($key,$item)
    {
        $this->args[$key] = $item;
    }

    public function getArg($key)
    {
        if(isset($this->args[$key])){
            return $this->args[$key];
        }else{
            return null;
        }
    }
}