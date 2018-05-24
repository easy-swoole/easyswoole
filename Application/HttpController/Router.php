<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/3
 * Time: 0:48
 */

namespace App\HttpController;


use FastRoute\RouteCollector;

class Router extends \EasySwoole\Core\Http\AbstractInterface\Router
{
    function register(RouteCollector $routeCollector)
    {
        // TODO: Implement register() method.
//        $routeCollector->get('/',function (Request $request ,Response $response){
//            $response->write('this router index');
//        });
//        $routeCollector->get('/test',function (Request $request ,Response $response){
//            $response->write('this router test');
//            $response->end();
//        });
//        $routeCollector->get( '/user/{id:\d+}',function (Request $request ,Response $response,$id){
//            $response->write("this is router user ,your id is {$id}");
//            $response->end();
//        });
//        $routeCollector->get( '/user2/{id:\d+}','/Api/test2');
        $routeCollector->get( '/','/api/test/test');
    }

}