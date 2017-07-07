<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/7
 * Time: 下午2:30
 */

namespace Core\Component\Version;


use Core\Http\Request;
use Core\Http\Response;
use Core\UrlParser;

class Control
{
    private $map = array();
    private $defaultHandler;
    /*
     * handler if match a version，must return boolean true
     */
    function addVersion($version,\Closure $handler){
        $temp = new Version();
        $this->map[$version] = array(
            "handler"=>$handler,
            'version'=>$temp
        );
        return $temp;
    }
    function startControl(){
        $request = Request::getInstance();
        $response = Response::getInstance();
        if(!$request->getAttribute("version")){
            //如果已经处于版本控制后的请求，则不再做重新匹配
            $target = null;
            foreach ($this->map as $version => $item){
                $flag = call_user_func($item['handler'],$request,$response);
                if($flag){
                    $target = $item;
                    break;
                }
            }
            $pathInfo = UrlParser::pathInfo();
            if($target){
                $request->withAttribute("version",$version);
                $realPath = $target['version']->getPathMap($pathInfo);
                if(is_string($realPath)){
                    $response->forward($realPath);
                }else if ($realPath instanceof \Closure){
                    call_user_func($realPath,$request,$response);
                }else{
                    $handler = $target['version']->getDefaultHandler();
                    if(is_string($handler)){
                        $response->forward($handler);
                    }else if ($handler instanceof \Closure){
                        call_user_func($handler,$request,$response);
                    }else{
                        $this->defaultHandler($request,$response);
                    }
                }
                //在没有做任何响应的时候，交给defaultHandler
                if(empty($response->getStatusCode()) && $response->getBody()){
                    $this->defaultHandler($request,$response);
                }
            }
            if($target !== null){
                $response->end();
            }
        }
    }
    function setDefaultHandler($defaultPathOrClosureHandler){
        $this->defaultHandler = $defaultPathOrClosureHandler;
        return $this;
    }
    private function defaultHandler(Request $request,Response $response){
        if(is_string($this->defaultHandler)){
            $response->forward($this->defaultHandler);
        }else if($this->defaultHandler instanceof \Closure){
            call_user_func($this->defaultHandler,$request,$response);
        }
    }
}