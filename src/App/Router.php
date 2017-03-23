<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/19
 * Time: 上午10:51
 */

namespace App;


use Core\AbstractInterface\AbstractRouter;
use Core\Http\Response\Response;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{

    function addRouter(RouteCollector $routeCollector)
    {
        // TODO: Implement addRouter() method.
        $routeCollector->addRoute("GET","/router",function (){
            Response::getInstance()->write("this is router");
            Response::getInstance()->end();
        });
    }
}