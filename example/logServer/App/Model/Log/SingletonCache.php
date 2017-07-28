<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/28
 * Time: 下午7:32
 */

namespace App\Model\Log;


class SingletonCache
{
    private static $instance;
    private $logs = array();
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new SingletonCache();
        }
        return self::$instance;
    }
    function add($msg){
        array_push($this->logs,$msg);
    }
    function size(){
        return count($this->logs);
    }
    function allLog(){
        return $this->logs;
    }
    function clear(){
        $this->logs = array();
    }

}