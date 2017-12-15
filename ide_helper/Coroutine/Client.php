<?php
/**
 * swoole-ide-helper
 * Author: eagle <eaglewudi@gmail.com>
 * Datetime: 20/07/2017
 */

namespace Swoole\Coroutine;


class Client
{

    /**
     * 连接到远程服务器
     * connect操作会有一次协程切换开销，connect发起时yield，完成时resume
     * @link https://wiki.swoole.com/wiki/page/588.html
     *
     * @param string $host 远程服务器的地址
     * @param int $port 远程服务器端口
     * @param float $timeout 是网络IO的超时，包括connect/send/recv，单位是s，支持浮点数。默认为0.1s，即100ms，超时发生时，连接会被自动close掉
     * @return bool
     */
    public function connect($host, $port, $timeout = 0.1)
    {
        return true;
    }

    /**
     * 发送数据
     * @link https://wiki.swoole.com/wiki/page/660.html
     *
     * @param string $data 发送的数据，必须为字符串类型，支持二进制数据
     * @return bool
     */
    public function send($data)
    {
        return true;
    }

    /**
     * 从服务器端接收数据
     *
     * 底层会自动yield，等待数据接收完成后自动切换到当前协程。
     * @link https://wiki.swoole.com/wiki/page/661.html
     *
     * @return string
     */
    public function recv()
    {
        return "";
    }

    /**
     * 关闭连接
     * 不存在阻塞，会立即返回
     * @link https://wiki.swoole.com/wiki/page/662.html
     *
     * @return bool  执行成功返回true，失败返回false
     */
    public function close()
    {
        return true;
    }

}