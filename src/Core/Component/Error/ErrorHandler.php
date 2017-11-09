<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/11/9
 * Time: 下午7:03
 */

namespace Core\Component\Error;


use Core\AbstractInterface\ErrorHandlerInterface;
use Core\Component\Logger;
use Core\Http\Request;
use Core\Http\Response;

class ErrorHandler  implements ErrorHandlerInterface
{
    function handler($msg, $trace)
    {
        // TODO: Implement handler() method.
    }

    function display($msg, $trace)
    {
        // TODO: Implement display() method.
        //判断是否在HTTP模式下
        if(Request::getInstance()){
            Response::getInstance()->write($msg);
        }else{
            Logger::getInstance('error')->console($msg,false);
        }
    }

    function log($msg, $trace)
    {
        // TODO: Implement log() method.
        Logger::getInstance('error')->log($msg);
    }

}