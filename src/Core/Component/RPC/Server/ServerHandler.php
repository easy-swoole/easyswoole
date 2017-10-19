<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/19
 * Time: 下午1:53
 */

namespace Core\Component\RPC\Server;


class ServerHandler
{
    protected $actionList = [];
    function registerAction($name,callable $callback){
        $this->actionList[$name] = $callback;
        return $this;
    }

    function setDefaultAction(callable $callback){
        $this->actionList['__DEFAULT__'] = $callback;
        return $this;
    }

    function getAction($name){
        if(isset($this->actionList[$name])){
            return $this->actionList[$name];
        }else{
            return null;
        }
    }
}