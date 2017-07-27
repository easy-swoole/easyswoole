<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/1
 * Time: 上午12:23
 */

namespace Core\Component;


class Di
{
    /*
     * 借以实现IOC注入
     */
    protected static $instance;
    protected $container = array();
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }


    function set($key, $obj,array $params = array(),$singleton = true){
        /*
         * 注入的时候不做任何的类型检测与转换
         * 由于编程人员为问题，该注入资源并不一定会被用到
         */
        $this->container[$key] = array(
            "obj"=>$obj,
            "params"=>$params,
            "singleton"=>$singleton
        );
        return $this;
    }

    function delete($key){
        unset( $this->container[$key]);
    }

    function clear(){
        $this->container = array();
    }

    /**
     * @param $key
     * @return mixed
     */
    function get($key){
        if(isset($this->container[$key])){
            $result = $this->container[$key];
            if(is_object($result['obj'])){
                return $result['obj'];
            }else if(is_callable($result['obj'])){
                $ret =  call_user_func_array($result['obj'],$result['params']);
                if($result['singleton']){
                    $this->set($key,$ret);
                }
                return $ret;
            }else if(is_string($result['obj']) && class_exists($result['obj'])){
                $reflection = new \ReflectionClass ( $result['obj'] );
                $ins =  $reflection->newInstanceArgs ( $result['params'] );
                if($result['singleton']){
                    $this->set($key,$ins);
                }
                return $ins;
            }else{
                return $result['obj'];
            }
        }else{
            return null;
        }
    }
}