<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/22
 * Time: 下午11:26
 */

namespace Core\Http;


use Core\Utility\Validate\Rules;
use Core\Utility\Validate\Verify;

class Request
{
    private static $instance;
    private  $swoole_http_request = null;
    static function getInstance(\swoole_http_request $request = null){
        if($request !== null){
            self::$instance = new Request($request);
        }
        return self::$instance;
    }
    function __construct(\swoole_http_request $request)
    {
        $this->swoole_http_request = $request;
    }
    function getGet($key = null){
        if($key === null){
            //不一定是带有get
            if(isset($this->swoole_http_request->get)){
                return $this->swoole_http_request->get;
            }else{
                return array();
            }
        }
        if(isset($this->swoole_http_request->get[$key])){
            return $this->swoole_http_request->get[$key];
        }else{
            return null;
        }
    }
    function getPost($key = null){
        if($key === null){
            //不一定是带有post
            if(isset($this->swoole_http_request->post)){
                return $this->swoole_http_request->post;
            }else{
                return array();
            }
        }
        if(isset($this->swoole_http_request->post[$key])){
            return $this->swoole_http_request->post[$key];
        }else{
            return null;
        }
    }
    function getRequestParam($key = null){
        $ret = $this->getPost($key);
        if(empty($ret)){
            $ret = $this->getGet($key);
        }
        return $ret;
    }
    function file(){
        return new File();
    }
    function getCookie($key = null){
        if($key === null){
            //不一定是带有cookie
            if(isset($this->swoole_http_request->cookie)){
                return $this->swoole_http_request->cookie;
            }else{
                return array();
            }
        }
        if(isset($this->swoole_http_request->cookie[$key])){
            return $this->swoole_http_request->cookie[$key];
        }else{
            return null;
        }
    }

    function getServer($key = null){
        if(empty($key)){
            return $this->swoole_http_request->server;
        }else{
            $key = strtolower($key);
            if(isset($this->swoole_http_request->server[$key])){
                return $this->swoole_http_request->server[$key];
            }else{
                return null;
            }
        }
    }

    function getHeader($key = null){
        if(empty($key)){
            return $this->swoole_http_request->header;
        }else{
            $key = strtolower($key);
            if(isset($this->swoole_http_request->header[$key])){
                return $this->swoole_http_request->header[$key];
            }else{
                return null;
            }
        }
    }

    function swooleRequest(){
        return $this->swoole_http_request;
    }
    function getRequestProperty($property){
        if(isset($this->swoole_http_request->$property)){
            return $this->swoole_http_request->$property;
        }else{
            return null;
        }
    }
    function setRequestProperty($property,$data){
        $this->swoole_http_request->$property = $data;
    }
    function getRequestParamsWithVerify(Rules $rules){
        return new Verify($this->getRequestParam(),$rules);
    }
}