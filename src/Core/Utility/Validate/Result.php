<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/27
 * Time: 下午12:40
 */

namespace Core\Utility\Validate;


class Result
{
    private $validateResult;
    private $errorInfo;
    function __construct($validateResult,$errorInfo)
    {
        if(empty($errorInfo)){
            $this->validateResult = $validateResult;
        }
        $this->errorInfo = $errorInfo;

    }

    function hasError(){
        return empty($this->errorInfo) ? false : true;
    }

    function error(){
        return new Error($this->errorInfo);
    }

    function validateResult(){
        return $this->validateResult;
    }
}