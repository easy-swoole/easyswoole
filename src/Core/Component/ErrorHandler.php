<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/19
 * Time: 上午9:51
 */

namespace Core\Component;


use Core\AbstractInterface\ErrorHandlerInterface;
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
            Logger::console($error,0);
        }

    }

    function log(SplError $error)
    {
        // TODO: Implement log() method.
        Logger::log($error);
    }
}