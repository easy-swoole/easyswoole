<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午6:35
 */

namespace EasySwoole\Core\AbstractInterface;


use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

abstract class AbstractController
{
    private $request;
    private $response;
    final public function __hook(string $actionName,Request $request,Response $response):void
    {
        $this->request = $request;
        $this->response = $response;
    }

    final public function getRequest():Request
    {
        return $this->request;
    }

    final public function getResponse():Response
    {
        return $this->response;
    }
}