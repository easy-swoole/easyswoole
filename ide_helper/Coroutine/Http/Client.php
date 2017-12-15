<?php
/**
 * swoole-ide-helper
 * Author: Wudi <wudi@51idc.com>
 * Datetime: 20/07/2017
 */

namespace Swoole\Coroutine\Http;


class Client
{

    /**
     * 存储上次请求的返回包体
     * @link https://wiki.swoole.com/wiki/page/578.html
     *
     * @var string
     */
    public $body;

    /**
     * 错误码
     * @link https://wiki.swoole.com/wiki/page/578.html
     *
     * @var integer
     */
    public $errCode;

    /**
     * 发起 GET 请求
     * @link https://wiki.swoole.com/wiki/page/582.html
     *
     * @param string $path 设置URL路径，如/index.html，注意这里不能传入http://domain
     */
    public function get($path)
    {

    }

    /**
     * 发起 POST 请求
     * @link https://wiki.swoole.com/wiki/page/583.html
     *
     * @param string $path 设置URL路径，如/index.html，注意这里不能传入http://domain
     * @param mixed $data 请求的包体数据，如果 $data 为数组底层自动会打包为 x-www-form-urlencoded 格式的 POST 内容，
     *                    并设置 Content-Type 为 application/x-www-form-urlencoded
     */
    public function post($path, $data)
    {

    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

}