<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午6:32
 */

namespace EasySwoole\Core\Http;


use EasySwoole\Core\AbstractInterface\Singleton;

class Response
{
    private $response;
    use Singleton;

    final public function __construct(\swoole_http_response $response)
    {
        $this->response = $response;
        self::$instance = $this;
    }
}