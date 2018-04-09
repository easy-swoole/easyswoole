<?php
/**
 * Created by PhpStorm.
 * User: 111
 * Date: 2018/4/9
 * Time: 14:45
 */

namespace App;


use EasySwoole\Core\Http\AbstractInterface\ExceptionHandlerInterface;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

/**
 * 统一异常处理, 包括想屏蔽异常和记录异常, 这里在生产环境需要屏蔽所有异常, 同时将异常写入日志
 * Class ExceptionHandler
 * @package App
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(\Throwable $throwable, Request $request, Response $response)
    {
        //统一异常处理 TODO: Implement handle() method.
    }

}