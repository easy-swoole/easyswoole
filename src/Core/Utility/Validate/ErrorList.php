<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/3
 * Time: 下午1:04
 */

namespace EasySwoole\Core\Utility\Validate;


class ErrorList
{
    private $error = [];

    function hasError():bool
    {
        return !empty($this->error);
    }

    function getError($filed):?ErrorBean
    {
        if(isset($this->error[$filed])){
            return $this->error[$filed];
        }else{
            return null;
        }
    }

    function all():array
    {
        return $this->error;
    }

    function first():ErrorBean
    {
        foreach ($this->error as $item){
            return $item;
        }
        //防止未经判断调用
        return new ErrorBean();
    }

    function addError($filed,ErrorBean $bean):ErrorList
    {
        $this->error[$filed] = $bean;
        return $this;
    }
}