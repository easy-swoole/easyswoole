<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/8
 * Time: 上午12:48
 */

namespace EasySwoole\Core\AbstractInterface;


use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

interface HttpExceptionHandlerInterface
{
    public function handle(\Exception $exception,Request $request,Response $response);
}