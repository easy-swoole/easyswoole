<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午5:49
 */

namespace EasySwoole\Core\Http;


use EasySwoole\Core\AbstractInterface\AbstractRouter;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Http\Message\Status;
use FastRoute\Dispatcher\GroupCountBased;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\SysConst;
use FastRoute\RouteCollector;

class Dispatcher
{
    use Singleton;
    private $controllerNameSpacePrefix;
    private $router = null;
    function __construct()
    {
        $this->controllerNameSpacePrefix = Di::getInstance()->get(SysConst::APP_NAMESPACE).'\\Controller';
        $collector = $this->checkRouter();
        if($collector){
            $this->router = new GroupCountBased($collector->getData());
        }
    }

    public function dispatch(Request $request,Response $response):void
    {
        if(!$response->isEndResponse()){
            $this->router($request,$response);
        };
    }

    private function checkRouter():?RouteCollector
    {
        $class = $this->controllerNameSpacePrefix.'\\Router';
        if(class_exists($class)){
            $router = new $class;
            if($router instanceof AbstractRouter){
                return $router->getRouteCollector();
            }else{
                return null;
            }
        }else{
            return null;
        }
    }

    private function router(Request $request,Response $response):void
    {
        if($this->router){
            $routeInfo = $this->router->dispatch($request->getMethod(),UrlParser::pathInfo($request->getUri()->getPath()));
            if($routeInfo !== false){
                switch ($routeInfo[0]) {
                    case \FastRoute\Dispatcher::NOT_FOUND:
                        break;
                    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                        $response->withStatus(Status::CODE_METHOD_NOT_ALLOWED);
                        break;
                    case \FastRoute\Dispatcher::FOUND:
                        $handler = $routeInfo[1];
                        $vars = $routeInfo[2];
                        if(is_callable($handler)){
                            //这里需要传递对象
                            call_user_func_array($handler,array_merge([$request,$response],array_values($vars)));
                        }else if(is_string($handler)){
                            $data = $request->getQueryParams();
                            $request->withQueryParams($vars+$data);
                            $pathInfo = UrlParser::pathInfo($handler);
                            $request->getUri()->withPath($pathInfo);
                        }
                        break;
                }
            }
        }
    }



}