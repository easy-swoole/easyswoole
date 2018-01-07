<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/2
 * Time: 下午10:11
 */

namespace EasySwoole\Core\Component;


use EasySwoole\Core\AbstractInterface\Singleton;

class Di
{
    use Singleton;
    private $container = array();

    public function set($key, $obj,...$arg):void
    {
        if(count($arg) == 1 && is_array($arg[0])){
            $arg = $arg[0];
        }
        /*
         * 注入的时候不做任何的类型检测与转换
         * 由于编程人员为问题，该注入资源并不一定会被用到
         */
        $this->container[$key] = array(
            "obj"=>$obj,
            "params"=>$arg,
        );
    }

    function delete($key):void
    {
        unset( $this->container[$key]);
    }

    function clear():void
    {
        $this->container = array();
    }

    function get($key)
    {
        if(isset($this->container[$key])){
            $result = $this->container[$key];
            if(is_object($result['obj'])){
                return $result['obj'];
            }else if(is_callable($result['obj'])){
                return $this->container[$key]['obj'];
            }else if(is_string($result['obj']) && class_exists($result['obj'])){
                $reflection = new \ReflectionClass ( $result['obj'] );
                $ins =  $reflection->newInstanceArgs ( $result['params'] );
                $this->container[$key]['obj'] = $ins;
                return $this->container[$key]['obj'];
            }else{
                return $result['obj'];
            }
        }else{
            return null;
        }
    }
}