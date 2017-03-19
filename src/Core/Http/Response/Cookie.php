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
        Response::getInstance()->getSwooleResponse()->cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
    function unsetCookie($name){
        $this->setCookie($name,null,time()-3600);
    }
}