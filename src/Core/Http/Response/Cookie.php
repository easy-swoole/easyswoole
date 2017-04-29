<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/19
 * Time: 上午9:32
 */

namespace Core\Http\Response;


class Cookie
{
    protected static $instance;
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new Cookie();
        }
        return self::$instance;
    }
    /*
     * expire 为null的时候  则被浏览器理解为会话模式
     */
    function setCookie($name, $value = null, $expire = null, $path = '/', $domain = null, $secure = null, $httpOnly = null){
        if(Response::getInstance()->isEndResponse()){
            return false;
        }else{
            Response::getInstance()->getSwooleResponse()->cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
            return true;
        }
    }
    function unsetCookie($name){
        if(Response::getInstance()->isEndResponse()){
            return false;
        }else{
            $this->setCookie($name,null,time()-3600);
            return true;
        }
    }
}