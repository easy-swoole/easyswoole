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

    /**
     * @param $key
     * @param $obj
     * @param array $params params for the obj call
     * @return $this
     */
    function set($key, $obj,array $params = array()){
        /*
         * 注入的时候不做任何的类型检测与转换
         * 由于编程人员为问题，该注入资源并不一定会被用到
         */
        $this->container[$key] = array(
            "obj"=>$obj,
            "params"=>$params
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
            /*
             * 注入的内容  防止二次被new 或call（单例）
             * 大多数业务场景为注入对象，因此优先判断。
             * 回调函数（闭包函数）不执行
             */
            if(is_object($result['obj'])){
                return $result['obj'];
            }else if($result['obj'] instanceof \Closure){
                return $result['obj'];
            }else if(is_callable($result['obj'])){
                $ret =  call_user_func_array($result['obj'],$result['params']);
                $this->set($key,$ret);
                return $ret;
            }else if(is_string($result['obj']) && class_exists($result['obj'])){
                $reflection = new \ReflectionClass ( $result['obj'] );
                $ins =  $reflection->newInstanceArgs ( $result['params'] );
                $this->set($key,$ins);
                return $ins;
            }else{
                return $result['obj'];
            }
        }else{
            return null;
        }
    }
}