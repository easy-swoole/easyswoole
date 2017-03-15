<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/4
 * Time: 上午11:51
 */

namespace Core\Component\Error;


use Core\AbstractInterface\AbstractErrorHandler;
use Core\Component\Logger;
use Core\Http\Request;
use Core\Http\Response;

class ErrorHandler extends AbstractErrorHandler
{

    function handler(Error $error)
    {
        // TODO: Implement handler() method.
    }

    function display(Error $error)
    {
        // TODO: Implement display() method.
        //if is in request ;
        $resp = Response::getInstance();
        if($resp){
            $request = Request::getInstance();
            $str = '';
            $str .= "<div style='text-align: center;'>";
            $str .= "<h2 style='color: rgb(190, 50, 50);'>Exception Occured:</h2>";
            $str .= "<table style='width: 800px; display: inline-block;'>";
            $str .= "<tr style='background-color:rgb(240,240,240);'><th>Request</th><td>{$request->getServer('PATH_INFO')}</td></tr>";
            $str .= "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Type</th><td style='width: 700px'>{$error->getErrorTypeInString()}</td></tr>";
            $str .= "<tr style='background-color:rgb(240,240,240);'><th>Message</th><td>{$error->getDescription()}</td></tr>";
            $str .= "<tr style='background-color:rgb(230,230,230);'><th>File</th><td>{$error->getFile()}</td></tr>";
            $str .= "<tr style='background-color:rgb(240,240,240);'><th>Line</th><td>{$error->getLine()}</td></tr>";
            $str .= "<tr style='background-color:rgb(240,240,240);'><th>Trace</th><td>{$error->traceToString('</br>')}</td></tr>";
            $str .= "</table></div>";
            $resp->write($str);
        }else{
            Logger::console($error,0);
        }
    }

    function log(Error $error)
    {
        // TODO: Implement log() method.
        Logger::log($error);
    }

}