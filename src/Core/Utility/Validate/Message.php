<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/25
 * Time: 下午3:56
 */

namespace Core\Utility\Validate;


class Message
{
    private $allMessages;
    function __construct(array $message)
    {
        $this->allMessages = $message;
    }
    function hasError(){
        return !empty($this->allMessages);
    }
    function all(){
        return $this->allMessages;
    }
    function get($col){
        if(isset($this->allMessages[$col])){
            return $this->allMessages[$col];
        }else{
            return array();
        }
    }
    //add by xhx 2017年08月23日17:11:09
    function first(){
        $message = array_shift($this->allMessages);
        $message = array_shift($message);
        return $message;
    }
}