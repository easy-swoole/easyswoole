<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/2
 * Time: 下午11:01
 */

namespace Core\Http\Session;


class Response extends Base
{
    function set($key,$default){
        if(!$this->session->isStart()){
            $this->session->start();
        }
        $data = $this->session->read();
        $data = unserialize($data);
        if(!is_array($data)){
           $data = [];
        }
        $data[$key] = $default;
        return $this->session->write(serialize($data));
    }
}