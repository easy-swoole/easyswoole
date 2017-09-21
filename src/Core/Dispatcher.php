<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:44
 */

namespace Core;


use Conf\Config;
use Conf\Event;
use Core\AbstractInterface\AbstractController;
use Core\AbstractInterface\AbstractRouter;
use Core\Component\Di;
use Core\Component\Sys\SysConst;
use Core\Component\SuperClosure;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Message\Status;
use FastRoute\Dispatcher\GroupCountBased;

class Dispatcher
{
    protected static $selfInstance;
    protected $fastRouterDispatcher;
    protected $controllerPool = array();
    protected $useControllerPool = false;
    protected $controllerMap = array();
    static function getInstance(){
        if(!isset(self::$selfInstance)){
            self::$selfInstance = new Dispatcher();
        }
        return self::$selfInstance;
    }

    function __construct()
    {
        $this->useControllerPool = Config::getInstance()->getConf("CONTROLLER_POOL");
    }

    function dispatch(){
        if(Response::getInstance()->isEndResponse()){
            return;
        }
        $pathInfo = UrlParser::pathInfo();
        $routeInfo = $this->doFastRouter($pathInfo,Request::getInstance()->getMethod());
        if($routeInfo !== false){
            switch ($routeInfo[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    // ... 404 NdoDispatcherot Found
                    break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    Response::getInstance()->withStatus(Status::CODE_METHOD_NOT_ALLOWED);
                    break;
                case \FastRoute\Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    if(is_callable($handler)){
                        call_user_func_array($handler,$vars);
                    }else if(is_string($handler)){
                        $pathInfo = UrlParser::pathInfo($handler);
                        Request::getInstance()->getUri()->withPath($pathInfo);
                    }
                    break;
            }
        }
        if(Response::getInstance()->isEndResponse()){
            return;
        }
        //去除为fastRouter预留的左边斜杠
        $pathInfo = ltrim($pathInfo,"/");
        if(isset($this->controllerMap[$pathInfo])){
            $finalClass = $this->controllerMap[$pathInfo]['finalClass'];
            $actionName = $this->controllerMap[$pathInfo]['actionName'];
        }else{
            /*
             * 此处用于防止URL恶意攻击，造成Dispatch缓存爆满。
             */
            if(count($this->controllerMap) > 50000){
                $this->controllerMap = [];
            }
            $list = explode("/",$pathInfo);
            $controllerNameSpacePrefix = "App\\Controller";
            $actionName = null;
            $finalClass = null;
            $controlMaxDepth = Di::getInstance()->get(SysConst::CONTROLLER_MAX_DEPTH);
            if(intval($controlMaxDepth) <= 0){
                $controlMaxDepth = 3;
            }
            $maxDepth = count($list) < $controlMaxDepth ? count($list) : $controlMaxDepth;
            while ($maxDepth > 0){
                $className = '';
                for ($i=0 ;$i<$maxDepth;$i++){
                    $className = $className."\\".ucfirst($list[$i]);//为一级控制器Index服务
                }
                if(class_exists($controllerNameSpacePrefix.$className)){
                    //尝试获取该class后的actionName
                    $actionName = empty($list[$i]) ? 'index' : $list[$i];
                    $finalClass = $controllerNameSpacePrefix.$className;
                    break;
                }else{
                    //尝试搜搜index控制器
                    $temp = $className."\\Index";
                    if(class_exists($controllerNameSpacePrefix.$temp)){
                        $finalClass = $controllerNameSpacePrefix.$temp;
                        //尝试获取该class后的actionName
                        $actionName = empty($list[$i]) ? 'index' : $list[$i];
                        break;
                    }
                }
                $maxDepth--;
            }
            if(empty($finalClass)){
                //若无法匹配完整控制器   搜搜Index控制器是否存在
                $finalClass = $controllerNameSpacePrefix."\\Index";
                $actionName = empty($list[0]) ? 'index' : $list[0];
            }
            $this->controllerMap[$pathInfo]['finalClass'] = $finalClass;
            $this->controllerMap[$pathInfo]['actionName'] = $actionName;
        }
        if(class_exists($finalClass)){
            if($this->useControllerPool){
                if(isset($this->controllerPool[$finalClass])){
                    $controller = $this->controllerPool[$finalClass];
                }else{
                    $controller = new $finalClass;
                    $this->controllerPool[$finalClass] = $controller;
                }
            }else{
                $controller = new $finalClass;
            }
            if($controller instanceof AbstractController){
                Event::getInstance()->onDispatcher(Request::getInstance(),Response::getInstance(),$finalClass,$actionName);
                //预防在进控制器之前已经被拦截处理
                if(!Response::getInstance()->isEndResponse()){
                    $controller->__call($actionName,null);
                }
            }else{
                Response::getInstance()->withStatus(Status::CODE_NOT_FOUND);
                trigger_error("controller {$finalClass} is not a instance of AbstractController");
            }
        }else{
            Response::getInstance()->withStatus(Status::CODE_NOT_FOUND);
            trigger_error("default controller Index not implement");
        }
    }

    private function intRouterInstance(){
        try{
            /*
                * if exit Router class in App directory
             */
            $ref = new \ReflectionClass("App\\Router");
            $router = $ref->newInstance();
            if($router instanceof AbstractRouter){
                $this->fastRouterDispatcher = new GroupCountBased($router->getRouteCollector()->getData());
            }
        }catch(\Exception $exception){
            //没有设置路由
            $this->fastRouterDispatcher = false;
        }
    }

    private function doFastRouter($pathInfo,$requestMethod){
        if(!isset($this->fastRouterDispatcher)){
            $this->intRouterInstance();
        }
        if($this->fastRouterDispatcher instanceof GroupCountBased){
            return $this->fastRouterDispatcher->dispatch($requestMethod,$pathInfo);
        }else{
            return false;
        }
    }
}