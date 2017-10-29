<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/29
 * Time: 下午9:36
 */

namespace Core\AbstractInterface;


use Core\Http\Request;
use Core\Http\Response;

interface HttpExceptionHandlerInterface
{
    function handler(\Exception $exception,Request $request , Response $response);
}