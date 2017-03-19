<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/19
 * Time: 上午9:51
 */

namespace Core\Component;


use Core\AbstractInterface\ErrorHandlerInterface;
use Core\Component\Object\Error;

class ErrorHandler implements ErrorHandlerInterface
{

    function handler(Error $error)
    {
        // TODO: Implement handler() method.
    }

    function display(Error $error)
    {
        // TODO: Implement display() method.
        Logger::console($error,0);
    }

    function log(Error $error)
    {
        // TODO: Implement log() method.
        Logger::log($error);
    }
}