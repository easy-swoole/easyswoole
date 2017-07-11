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
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{

    function addRouter(RouteCollector $routeCollector)
    {
        // TODO: Implement addRouter() method.
        $routeCollector->addRoute(['GET','POST'],"/router",function (){
            $this->response()->write("match router now");
            $this->response()->end();
        });
    }
}