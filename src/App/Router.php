<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/30
 * Time: 下午11:55
 */

namespace App;


use Core\AbstractInterface\AbstractRouter;
use Core\Component\Logger;
use Core\Http\Response;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{

    function addRouter(RouteCollector $routeCollector)
    {
        // TODO: Implement addRouter() method.
        $routeCollector->addRoute(['GET','POST'],"/router",function (){
            $res = Response::getInstance();
            $res->writeJson();
            $res->end();
        });
        $routeCollector->addRoute("GET","/router2",'/test');
    }
}