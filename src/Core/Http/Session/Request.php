<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/2
 * Time: 下午11:01
 */

namespace Core\Http\Session;


class Request extends Base
{
    function get($key,$default = null){
        if(!$this->session->isStart()){
            $this->session->start();
        }
        $data = $this->session->read();
        $data = unserialize($data);
        if(is_array($data)){
            if(isset($data[$key])){
                return $data[$key];
            }else{
                return $default;
            }
        }else{
            return $default;
        }
    }

    function toArray(){
        if(!$this->session->isStart()){
            $this->session->start();
        }
        $data = $this->session->read();
        $data = unserialize($data);
        if(is_array($data)){
            return $data;
        }else{
            return array();
        }
    }

}