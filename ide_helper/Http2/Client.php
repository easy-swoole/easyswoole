<?php
/**
 * swoole_http2_client
 * User: lushuncheng<admin@lushuncheng.com>
 * Date: 2017/3/1
 * Time: 18:17
 * @link https://github.com/lscgzwd
 * @copyright Copyright (c) 2017 Lu Shun Cheng (https://github.com/lscgzwd)
 * @licence http://www.apache.org/licenses/LICENSE-2.0
 * @author Lu Shun Cheng (lscgzwd@gmail.com)
 *
 *
 * @package Swoole\Http2
 * @version 1.0
 */
namespace Swoole\Http2;


class Client
{
    /**
     * Client constructor.
     * @param string $host
     * @param int $port
     * @param bool $useSSL
     */
    public function __construct(string $host, int $port, bool $useSSL = false)
    {
    }

    /**
     * @param array $haders ['key' => 'value']
     */
    public function setHeaders(array $haders)
    {

    }

    /**
     * @param array $cookies ['key'=>'value']
     */
    public function setCookies(array $cookies)
    {
    
    }

    /**
     * @param string $uri
     * @param callable $callback
     * @return bool|void
     */
    public function get(string $uri, callable $callback) {

    }

    /**
     * @param string $uri
     * @param callable $callback
     * @param mixed $data
     * @return void|bool
     */
    public function post(string $uri, callable $callback, mixed $data)
    {

    }

    /**
     * @param string $uri
     * @param callable $callback
     * @return int|bool|void  return the stream id when success
     */
    public function openStream(string $uri, callable $callback)
    {

    }

    /**
     * push data to server
     * @param int $streamID
     * @param mixed $data
     * @return bool
     */
    public function push(int $streamID, mixed $data)
    {

    }

    /**
     * @param int $streamID
     */
    public function closeStream(int $streamID) {

    }
}