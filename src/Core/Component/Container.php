<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午2:46
 */

namespace EasySwoole\Core\Component;


class Container
{
    private $container = [];

    function add($key,$item):Container
    {
        $this->container[$key] = $item;
        return $this;
    }

    function delete($key):Container
    {
        if(isset($this->container[$key])){
            unset($this->container[$key]);
        }
        return $this;
    }

    function get($key)
    {
        if(isset($this->container[$key])){
            return $this->container[$key];
        }else{
            return null;
        }
    }

    function all():array
    {
        return $this->container;
    }

}