<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午6:09
 */

namespace EasySwoole\Core\AbstractInterface;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;


abstract class AbstractRouter
{
    private $routeCollector;
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
}