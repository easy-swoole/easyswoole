<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/19
 * Time: 上午9:32
 */

namespace Core\Http\Request;


class Cookie
{
    protected static $instance;
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new Cookie();
        }
        return self::$instance;
    }
    function getCookie($key = null){
        $request = Request::getInstance()->getSwooleRequest();
        if($key === null){
            //不一定是带有cookie
            if(isset($request->cookie)){
                return $request->cookie;
            }else{
                return array();
            }
        }
        if(isset($request->cookie[$key])){
            return $request->cookie[$key];
        }else{
            return null;
        }
    }
}