<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/19
 * Time: 上午9:29
 */

namespace Core\Http\Response;


use Conf\Event;
use Core\Dispatcher;
use Core\Http\Request\Request;
use Core\Http\Status;

class Response
{
    private static $instance;
    private $isEndResponse = 0;
    private $swoole_http_response = null;
    static function getInstance(\swoole_http_response $response = null){
        if($response !== null){
            self::$instance = new Response($response);
        }
        return self::$instance;
    }
    function __construct(\swoole_http_response $response)
    {
        $this->swoole_http_response = $response;
    }

    /**
     * @param mixed $obj
     */
    function write($obj){
        if( is_array($obj) || is_object($obj)){
            $obj = json_encode($obj,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }
        /*
          * 禁止输出空字符串
       */
        if(strlen($obj)){
            $this->swoole_http_response->write($obj);
        }
    }
    function end(){
        if(!$this->isEndResponse){
            $this->swoole_http_response->end();
            $this->isEndResponse = 1;
            return true;
        }else{
            return false;
        }
    }
    function isEndResponse(){
        return $this->isEndResponse;
    }
    function sendHttpStatus($code) {
        $this->swoole_http_response->status($code);
    }

    function writeJson($httpCode,$result = null,$msg = null,$autoJsonHeader = 1){
        if($autoJsonHeader){
            $this->sendHttpStatus($httpCode);
            $this->sendHeader("Content-type","application/json;charset=utf-8");
        }
        $data = Array(
            "code"=>$httpCode,
            "result"=>$result,
            "msg"=>$msg
        );
        $this->write(json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }
    function redirect($url){
        //仅支持header重定向  不做meta定向
        $this->sendHttpStatus(Status::CODE_MOVED_TEMPORARILY);
        $this->sendHeader("Location",$url);
    }
    function forward($pathTo,array $get = array(),array $post = array(),array $cookies = array()){
        $serverData = Request::getInstance()->getServer();
        $serverData['path_info'] = $pathTo;
        Request::getInstance()->setSwooleRequestProperty("server",$serverData);
        Request::getInstance()->setSwooleRequestProperty("get",$get+ Request::getInstance()->getGet());
        Request::getInstance()->setSwooleRequestProperty("post",$post+ Request::getInstance()->getPost());
        Request::getInstance()->setSwooleRequestProperty("cookie",$cookies+ Request::getInstance()->cookie()->getCookie());
        Event::getInstance()->onRequest(Request::getInstance(),Response::getInstance());
        Dispatcher::getInstance()->dispatch();
    }
    function sendHeader($key,$val){
        $this->swoole_http_response->header($key,$val);
    }
    function getSwooleResponse(){
        return $this->swoole_http_response;
    }
    function cookie(){
        return Cookie::getInstance();
    }
}