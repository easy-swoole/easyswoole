<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午5:49
 */

namespace EasySwoole\Core\Http;


use EasySwoole\Core\AbstractInterface\AbstractController;
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

        if(!$response->isEndResponse()){
            $this->controllerHandler($request,$response);
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

    private function controllerHandler(Request $request,Response $response)
    {
        $pathInfo = ltrim($request->getUri()->getPath(),"/");
        $list = explode("/",$pathInfo);
        $actionName = null;
        $finalClass = null;
        $controlMaxDepth = Di::getInstance()->get(SysConst::CONTROLLER_MAX_DEPTH);
        $currentDepth = count($list);
        $maxDepth = $currentDepth < $controlMaxDepth ? $currentDepth : $controlMaxDepth;
        while ($maxDepth > 0){
            $className = '';
            for ($i=0 ;$i<$maxDepth;$i++){
                $className = $className."\\".ucfirst($list[$i]);//为一级控制器Index服务
            }
            if(class_exists($this->controllerNameSpacePrefix.$className)){
                //尝试获取该class后的actionName
                $actionName = empty($list[$i]) ? 'index' : $list[$i];
                $finalClass = $this->controllerNameSpacePrefix.$className;
                break;
            }else{
                //尝试搜搜index控制器
                $temp = $className."\\Index";
                if(class_exists($this->controllerNameSpacePrefix.$temp)){
                    $finalClass = $this->controllerNameSpacePrefix.$temp;
                    //尝试获取该class后的actionName
                    $actionName = empty($list[$i]) ? 'index' : $list[$i];
                    break;
                }
            }
            $maxDepth--;
        }
        if(empty($finalClass)){
            //若无法匹配完整控制器   搜搜Index控制器是否存在
            $finalClass = $this->controllerNameSpacePrefix."\\Index";
            $actionName = empty($list[0]) ? 'index' : $list[0];
        }
        if(class_exists($finalClass)){
            $controller = new $finalClass;
            if($controller instanceof AbstractController){
                $controller->__hook($actionName,$request,$response);
            }else{
                trigger_error("class@{$finalClass} not a controller class");
                $response->withStatus(Status::CODE_NOT_FOUND);
            }
        }else{
            $response->withStatus(Status::CODE_NOT_FOUND);
        }
    }



}