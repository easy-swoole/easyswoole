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

    function listen($event,callable $callback){
        $this->eventList[$event] = $callback;
        return $this;
    }

    function event($event,...$arg){
        if(isset($this->eventList[$event])){
            $handler = $this->eventList[$event];
            try{
                call_user_func_array($handler,$arg);
            }catch (\Exception $exception){
                throw $exception;
            }
        }
    }
}