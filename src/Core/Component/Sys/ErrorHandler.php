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
use Core\Http\Message\Status;
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
            $response2 = Response::getInstance();
            $response2->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
            $response2->withHeader("Content-Type","text/html;charset=utf-8");
            $response2->write(nl2br($error->__toString()));
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