<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/22
 * Time: 下午11:12
 */

namespace Core\Http;


use Conf\Event;
use Core\Dispatcher;

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
     * @param string $str
     */
    function write($str){
        if(!empty($str)){
            /*
             * 禁止输出空字符串
             */
            $this->swoole_http_response->write($str);
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
            self::sendHttpStatus($httpCode);
            self::sendHeader("Content-type","application/json;charset=utf-8");
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
        self::sendHttpStatus(Status::CODE_MOVED_TEMPORARILY);
        self::sendHeader("Location",$url);
    }
    function forward($pathTo,array $get = array(),array $post = array(),array $cookies = array()){
        $serverData = Request::getInstance()->getServer();
        $serverData['path_info'] = $pathTo;
        Request::getInstance()->setRequestProperty("server",$serverData);
        Request::getInstance()->setRequestProperty("get",$get+ Request::getInstance()->getGet());
        Request::getInstance()->setRequestProperty("post",$post+ Request::getInstance()->getPost());
        Request::getInstance()->setRequestProperty("cookie",$cookies+ Request::getInstance()->getCookie());
        Event::getInstance()->onRequest(Request::getInstance(),Response::getInstance());
        Dispatcher::getInstance()->dispatch();
    }
    /*
     * expire 为null的时候  则被浏览器理解为会话模式
     */
    function setCookie($name, $value = null, $expire = null, $path = '/', $domain = null, $secure = null, $httpOnly = null){
        $this->swoole_http_response->cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
    function unsetCookie($name){
        $this->setCookie($name,null,time()-3600);
    }
    function sendHeader($key,$val){
        $this->swoole_http_response->header($key,$val);
    }
}