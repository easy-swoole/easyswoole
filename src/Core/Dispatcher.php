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
use Core\Component\SysConst;
use Core\Component\SuperClosure;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Message\Status;
use FastRoute\Dispatcher\GroupCountBased;

class Dispatcher
{
    protected static $selfInstance;
    protected $fastRouterDispatcher;
    protected $currentApplicationDirectory;
    protected $controllerPool = array();
    protected $useControllerPool = false;
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
        $this->currentApplicationDirectory = Di::getInstance()->get(SysConst::APPLICATION_DIR);
        $pathInfo = UrlParser::pathInfo();
        $routeInfo = $this->doFastRouter($pathInfo,Request::getInstance()->getMethod());
        if($routeInfo !== false){
            switch ($routeInfo[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    // ... 404 NdoDispatcherot Found
                    break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    Response::getInstance()->withStatus(Status::CODE_METHOD_NOT_ALLOWED);
                    break;
                case \FastRoute\Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    if(is_callable($handler)){
                        call_user_func_array($handler,$vars);
                    }
                    break;
            }
        }
        if(Response::getInstance()->isEndResponse()){
            return;
        }
        //去除为fastRouter预留的左边斜杠
        $pathInfo = ltrim($pathInfo,"/");
        $list = explode("/",$pathInfo);
        $controllerNameSpacePrefix = "{$this->currentApplicationDirectory}\\Controller";
        $actionName = null;
        $finalClass = null;
        $controlMaxDepth = Di::getInstance()->get(SysConst::CONTROLLER_MAX_DEPTH);
        if(intval($controlMaxDepth) <= 0){
            $controlMaxDepth = 3;
        }
        $maxDepth = count($list) < $controlMaxDepth ? count($list) : $controlMaxDepth;
        $isIndexController = 0;
        while ($maxDepth > 0){
            $className = '';
            for ($i=0 ;$i<$maxDepth;$i++){
                $className = $className."\\".ucfirst($list[$i]);//为一级控制器Index服务
            }
            if(class_exists($controllerNameSpacePrefix.$className)){
                //尝试获取该class后的actionName
                $actionName = isset($list[$i]) ? $list[$i] : '';
                $finalClass = $controllerNameSpacePrefix.$className;
                break;
            }else{
                //尝试搜搜index控制器
                $temp = $className."\\Index";
                if(class_exists($controllerNameSpacePrefix.$temp)){
                    $finalClass = $controllerNameSpacePrefix.$temp;
                    //尝试获取该class后的actionName
                    $actionName = isset($list[$i]) ? $list[$i] : '';
                    break;
                }
            }
            $maxDepth--;
        }
        if(empty($finalClass)){
            //若无法匹配完整控制器   搜搜Index控制器是否存在
            $finalClass = $controllerNameSpacePrefix."\\Index";
            $isIndexController = 1;
        }
        if(class_exists($finalClass)){
            if($isIndexController){
                $actionName = isset($list[0]) ? $list[0] : '';
            }
            $actionName = $actionName ? $actionName : "index";
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
            $ref = new \ReflectionClass("{$this->currentApplicationDirectory}\\Router");
            $router = $ref->newInstance();
            if($router instanceof AbstractRouter){
                $is = $router->isCache();
                if($is){
                    $is = $is.".{$this->currentApplicationDirectory}";
                    if(file_exists($is)){
                        $dispatcherData = file_get_contents($is);
                        $dispatcherData = unserialize($dispatcherData);
                    }else{
                        $dispatcherData =  $router->getRouteCollector()->getData();
                        $cache =  $dispatcherData;
                        /*
                         * to support closure
                         */
                        array_walk_recursive($cache,function(&$item,$key){
                            if($item instanceof \Closure){
                                $item = new SuperClosure($item);
                            }
                        });
                        file_put_contents(
                            $is,
                            serialize($cache)
                        );
                    }
                    $this->fastRouterDispatcher[$this->currentApplicationDirectory] = new GroupCountBased($dispatcherData);
                }else{
                    $this->fastRouterDispatcher[$this->currentApplicationDirectory] = new GroupCountBased($router->getRouteCollector()->getData());
                }
            }
        }catch(\Exception $exception){
            //没有设置路由
            $this->fastRouterDispatcher[$this->currentApplicationDirectory] = false;
        }
    }

    private function doFastRouter($pathInfo,$requestMethod){
        //判断是否建立过当前应用目录的快速路由
        if(!isset($this->fastRouterDispatcher[$this->currentApplicationDirectory])){
            $this->intRouterInstance();
        }
        $dispatcher = $this->fastRouterDispatcher[$this->currentApplicationDirectory];
        if($dispatcher instanceof GroupCountBased){
            return $dispatcher->dispatch($requestMethod,$pathInfo);
        }else{
            return false;
        }
    }
}