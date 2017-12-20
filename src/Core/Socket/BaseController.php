<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/20
 * Time: 下午3:55
 */

namespace EasySwoole\Core\Socket;


use EasySwoole\Core\Component\Lib\Stream;

abstract class BaseController
{
    private $response;

    function __construct($actionName)
    {
        $this->response = new Stream();
    }

    /*
     * 返回false的时候为拦截
     */
    public function onRequest():bool
    {
        return true;
    }

    function getResponse():Stream
    {
        return $this->response;
    }

    function write(string $message):void
    {
        $this->response->write($message);
    }

    abstract function client();
}