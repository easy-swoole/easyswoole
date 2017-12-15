<?php

namespace Swoole\Http;

/**
 * swoole_http_client
 *
 * Author: EagleWu <eaglewudi@gmail.com>
 * Date: 2016/02/17
 */
class Client
{
    public $cookies;
    public $headers;
    public $body;
    public $statusCode; // -1 连接服务器超时 -2 服务器响应超时

    public $setting;

    public $set_headers;

    /**
     * swoole_http_client constructor.
     * @param string $host
     * @param integer $port
     */
    public function __construct($host, $port)
    {

    }

    /**
     * @param $setting
     * @return true
     */
    public function set($setting)
    {
    }

    /**
     * @param $headers
     * @return true
     */
    public function setHeaders($headers)
    {

    }

    /**
     * @param $cookies
     * @return true
     */
    public function setCookies($cookies)
    {

    }

    /**
     * @param $data
     * @return true
     */
    public function setData($data)
    {

    }

    /**
     * @param string $uri
     * @param mixed $finish
     * @return bool
     */
    public function execute($uri, $finish)
    {

    }

    /**
     * @param $data
     * @param int $opcode
     * @param int $fin
     */
    public function push($data, $opcode = WEBSOCKET_OPCODE_TEXT, $fin = 1)
    {

    }

    /**
     * @return boolean
     */
    public function isConnected()
    {

    }

    /**
     * @return bool
     */
    public function close()
    {

    }

    /**
     * @param string $name
     * @param mixed $callback
     */
    public function on($name, $callback)
    {

    }

    /**
     * @param string $uri
     * @param mixed $finish
     */
    public function get($uri, $finish)
    {

    }

    /**
     * @param string $uri
     * @param mixed $post
     * @param mixed $finish
     */
    public function post($uri, $post, $finish)
    {

    }

    /**
     * @param string $uri
     * @param mixed $finish
     */
    public function upgrade($uri, $finish)
    {

    }

    /**
     * @param $file
     */
    public function addFile($file)
    {

    }

    public function __destruct()
    {

    }
}