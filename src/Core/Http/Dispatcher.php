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
use EasySwoole\Core\Component\Container;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\SysConst;
use FastRoute\RouteCollector;

class Dispatcher
{
    use Singleton;
    private $controllerPool;
    private $controllerNameSpacePrefix;
    private $router;
    function __construct()
    {
        $this->controllerNameSpacePrefix = Di::getInstance()->get(SysConst::APP_NAMESPACE).'\\Controller';
        $this->controllerPool = new Container();

    }

    public function dispatch(Request $request,Response $response):void
    {
        $pathInfo = UrlParser::pathInfo($request->getUri()->getPath());
    }

    private function checkRouter():?RouteCollector
    {
        if(class_exists($this->controllerNameSpacePrefix.'\\Router')){
            $router = new $this->controllerNameSpacePrefix.'\\Router';
            if($router instanceof AbstractRouter){

            }else{

            }
        }else{
            return null;
        }
    }



}