<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/5
 * Time: 上午11:39
 */

namespace Core\Utility\Validate;


class Message
{
    private $error;
    function __construct(array $error)
    {
        $this->error = $error;
    }

    function hasError(){
        return !empty($this->error);
    }

    function getError($filed){
        if(isset($this->error[$filed])){
            return new Error($this->error[$filed]);
        }else{
            /*
             * 预防调用错误
             */
            return new Error(array());
        }
    }
    function all(){
        return $this->error;
    }
}