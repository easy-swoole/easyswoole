<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午3:46
 */

namespace Core\Component\RPC\Common;


class ActionList
{
    private $list = [];

    function registerAction($name,callable $call){
        $this->list[$name] = $call;
    }

    function getHandler($name){
        if(isset($this->list[$name])){
            return $this->list[$name];
        }else{
            return null;
        }
    }
}