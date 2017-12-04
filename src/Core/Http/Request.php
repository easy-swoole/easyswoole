<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午6:32
 */

namespace EasySwoole\Core\Http;


use EasySwoole\Core\AbstractInterface\Singleton;

class Request
{
    private $request;
    use Singleton;

    final public function __construct(\swoole_http_request $request)
    {
        $this->request = $request;
        self::$instance = $this;
    }
}