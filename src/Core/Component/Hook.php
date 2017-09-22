<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/23
 * Time: 上午12:05
 */

namespace Core\Component;


use Core\Swoole\AsyncTaskManager;
use Core\Swoole\Server;

class Hook
{
    protected static $instance;
    private $eventList = [];
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    function listen($event,$callback,$params = []){
        $this->eventList[$event] = array(
            'handler'=>$callback,
            'params'=>$params
        );
        return $this;
    }

    function event($event,$async = true){
        if(isset($this->eventList[$event])){
            $handler = $this->eventList[$event];
            if($async && Server::getInstance()->isStart()){
                AsyncTaskManager::getInstance()->add(function ()use($handler){
                    try{
                        call_user_func_array($handler['handler'],$handler['params']);
                    }catch (\Exception $exception){
                        throw $exception;
                    }
                });
            }else{
                try{
                    call_user_func_array($handler['handler'],$handler['params']);
                }catch (\Exception $exception){
                    throw $exception;
                }
            }
        }
    }
}