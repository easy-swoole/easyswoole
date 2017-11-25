<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/11/22
 * Time: 下午10:08
 */

namespace Core\Component\Version;


class VersionList
{
    private $list = [];
    function add($name,callable $judge){
        $version = new Version($name,$judge);
        $this->list[$name] = $version;
        return $version;
    }

    function get($name){
        if(isset($this->list[$name])){
            return $this->list[$name];
        }
        return null;
    }

    function all(){
        return $this->list;
    }
}