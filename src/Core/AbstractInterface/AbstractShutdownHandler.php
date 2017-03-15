<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/21
 * Time: 上午11:39
 */

namespace Core\AbstractInterface;


abstract class AbstractShutdownHandler
{
    protected $lastError;//error_get_last();
    function __construct()
    {
        $this->lastError = error_get_last();
    }
    function getLastError(){
        return $this->lastError;
    }
    abstract function handler();
}