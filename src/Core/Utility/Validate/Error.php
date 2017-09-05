<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/5
 * Time: 上午11:56
 */

namespace Core\Utility\Validate;


class Error
{
    private $error;
    function __construct(array $error)
    {
        $this->error = $error;
    }

    function first(){
        return array_shift($this->error);
    }

    function all(){
        return $this->error;
    }
}