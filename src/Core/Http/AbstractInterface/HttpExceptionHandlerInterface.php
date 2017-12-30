<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/29
 * Time: 下午7:29
 */

namespace EasySwoole\Core\Http\AbstractInterface;


use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

interface HttpExceptionHandlerInterface
{
    public function handle(\Exception $exception,Request $request,Response $response);
}