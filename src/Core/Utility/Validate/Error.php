<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/27
 * Time: 下午12:42
 */

namespace Core\Utility\Validate;


class Error
{
    private $error;
    function __construct($error)
    {
        $this->error = $error;
    }

    function allErrorColumns(){
        return array_keys($this->error);
    }

    function column($col){
        if(isset($this->error[$col])){
            return $this->error[$col];
        }else{
            return null;
        }
    }

}