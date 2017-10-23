<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午3:48
 */

namespace Core\Component\RPC\Server;

class Service
{
    protected $actionRegisterClass;
    public function getActionRegisterClass()
    {
        return $this->actionRegisterClass;
    }

    public function setActionRegisterClass($actionRegisterClass)
    {
        $this->actionRegisterClass = $actionRegisterClass;
    }
}