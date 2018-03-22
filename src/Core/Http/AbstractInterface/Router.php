<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/29
 * Time: 下午7:29
 */

namespace EasySwoole\Core\Http\AbstractInterface;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;

abstract class Router
{
    private $routeCollector;
    private $methodNotAllowCallBack = null;
    final function __construct()
    {
        $this->routeCollector = new RouteCollector(new Std(),new GroupCountBased());
        $this->register($this->routeCollector);
    }

    abstract function register(RouteCollector $routeCollector);

    function getRouteCollector():RouteCollector
    {
        return $this->routeCollector;
    }


    function setMethodNotAllowCallBack(callable $call)
    {
        $this->methodNotAllowCallBack = $call;
    }

    function getMethodNotAllowCallBack()
    {
        return $this->methodNotAllowCallBack;
    }
}