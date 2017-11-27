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
    function handler($msg,$file = null,$line = null,$errorCode = null, $trace)
    {
        // TODO: Implement handler() method.
    }

    function display($msg,$file = null,$line = null,$errorCode = null, $trace)
    {
        // TODO: Implement display() method.
        //判断是否在HTTP模式下
        if(Request::getInstance()){
            Response::getInstance()->write(nl2br($msg) ." in file {$file} line {$line}");
        }else{
            Logger::getInstance('error')->console($msg." in file {$file} line {$line}",false);
        }
    }

    function log($msg,$file = null,$line = null,$errorCode = null, $trace)
    {
        // TODO: Implement log() method.
        Logger::getInstance('error')->log($msg." in file {$file} line {$line}");
    }

}