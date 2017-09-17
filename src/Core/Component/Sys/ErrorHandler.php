<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/10
 * Time: 下午5:09
 */

namespace Core\Component\Sys;


use Core\AbstractInterface\ErrorHandlerInterface;
use Core\Component\Logger;
use Core\Component\Spl\SplError;
use Core\Http\Request;
use Core\Http\Response;

class ErrorHandler implements ErrorHandlerInterface
{

    function handler(SplError $error)
    {
        // TODO: Implement handler() method.
    }

    function display(SplError $error)
    {
        // TODO: Implement display() method.
        if(Request::getInstance()){
            Response::getInstance()->write($error->__toString());
        }else{
            Logger::getInstance()->console($error,0);
        }
    }

    function log(SplError $error)
    {
        // TODO: Implement log() method.
        Logger::getInstance('error')->log($error);
    }
}