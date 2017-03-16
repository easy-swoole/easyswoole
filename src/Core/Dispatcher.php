<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:44
 */

namespace Core;


use Conf\Event;
use Core\AbstractInterface\AbstractController;
use Core\AbstractInterface\AbstractRouter;
use Core\Component\Di;
use Core\Component\SysConst;
use Core\Http\Request;
use Core\Http\Response;
use Core\Component\SuperClosure;
use Core\Http\Status;
use FastRoute\Dispatcher\GroupCountBased;
use Core\Component\RouteCollector;

class Dispatcher
{
    protected static $selfInstance;
    protected $fastRouterDispatcher;
    static function getInstance(){
        if(!isset(self::$selfInstance)){
            self::$selfInstance = new Dispatcher();
        }
        return self::$selfInstance;
    }

    function __construct()
    {
        /*
            *initialize fast router
            * single try for new  Router in per Dispatcher instance
         */
        try{
            /*
                    * if exit Router class in App directory
               */
            $ref = new \ReflectionClass("App\\Router");
            $router = $ref->newInstance();
            if($router instanceof AbstractRouter){
                $is = $router->isCache();
                if($is){
                    if(file_exists($is)){
                        $dispatcherData = require_once "{$is}";
                        $dispatcherData = unserialize($dispatcherData);
                    }else{
                        $dispatcherData =  RouteCollector::getInstance()->getData();
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
                            "<?php return '" . serialize($cache) . "';"
                        );
                    }
                    $this->fastRouterDispatcher = new GroupCountBased($dispatcherData);
                }else{
                    $this->fastRouterDispatcher = new GroupCountBased(RouteCollector::getInstance()->getData());
                }
            }
        }catch(\Exception $exception){

        }
    }
    private function doFastRouter($pathInfo,$requestMethod){
        if($this->fastRouterDispatcher instanceof GroupCountBased){
            return $this->fastRouterDispatcher->dispatch($requestMethod,$pathInfo);
        }else{
            return false;
        }
    }
    function dispatch(){
        if(Response::getInstance()->isEndResponse()){
            return;
        }
        $pathInfo = UrlParser::parser();
        $routeInfo = $this->doFastRouter($pathInfo,Request::getInstance()->getServer("REQUEST_METHOD"));
        if($routeInfo !== false){
            switch ($routeInfo[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    // ... 404 NdoDispatcherot Found
                    break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    Response::getInstance()->sendHttpStatus(Status::CODE_METHOD_NOT_ALLOWED);
                    return;
                    break;
                case \FastRoute\Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    if(is_callable($handler)){
                        call_user_func_array($handler,$vars);
                        return;
                    }
                    break;
            }
        }

        //去除为fastRouter预留的左边斜杠
        $pathInfo = ltrim($pathInfo,"/");
        $list = explode("/",$pathInfo);
        $controllerNameSpacePrefix = "App\\Controller";
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
                $actionName = @$list[$i];
                $finalClass = $controllerNameSpacePrefix.$className;
                break;
            }else{
                //尝试搜搜index控制器
                $temp = $className."\\Index";
                if(class_exists($controllerNameSpacePrefix.$temp)){
                    $finalClass = $controllerNameSpacePrefix.$temp;
                    //尝试获取该class后的actionName
                    $actionName = @$list[$i];
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
                $actionName = @$list[0];
            }
            $actionName = $actionName ? $actionName : "index";
            $controller = new $finalClass;
            if($controller instanceof AbstractController){
                Event::getInstance()->onDispatcher(Request::getInstance(),Response::getInstance(),$finalClass,$actionName);
                //预防在进控制器之前已经被拦截处理
                if(!Response::getInstance()->isEndResponse()){
                    $controller->actionName($actionName);
                    $controller->onRequest($actionName);
                    //同上
                    if(!Response::getInstance()->isEndResponse()){
                        $controller->$actionName();
                        $controller->afterResponse();
                    }
                }
            }else{
                Response::getInstance()->sendHttpStatus(Status::CODE_NOT_FOUND);
                trigger_error("controller {$finalClass} is not a instance of AbstractController");
            }
        }else{
            Response::getInstance()->sendHttpStatus(Status::CODE_NOT_FOUND);
            trigger_error("default controller Index not implement");
        }
    }
}