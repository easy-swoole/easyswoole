<?php
/**
 * Created by PhpStorm.
 * User: Link
 * Date: 2017/10/12
 * Time: 14:27
 */

namespace Swoole\Redis;

/**
 * Redis服务器端
 *
 * Class Server
 * @package Swoole\Redis
 */
class Server extends \Swoole\Server
{

    const NIL    = 'NIL'; // 返回nil数据
    const ERROR  = 'ERROR'; // 返回错误码
    const STATUS = 'STATUS'; // 返回状态
    const INT    = 'INT'; // 返回整数，，format必须传入参数值，类型必须为整数
    const STRING = 'STRING'; // 返回字符串，format必须传入参数值，类型必须为字符串
    const SET    = 'SET'; // 返回列表，format必须传入参数值，类型必须为数组
    const MAP    = 'MAP'; // 返回Map，format必须传入参数值，类型必须为关联索引数组


    /**
     * 设置Redis命令字的处理器
     *
     * @param string   $command  命令的名称
     * @param callable $callback 命令的处理函数，回调函数返回字符串类型时会自动发送给客户端
     *                           返回的数据必须为Redis格式，可使用format静态方法进行打包
     *
     * @return void
     */
    public function setHandler(string $command, callable $callback)
    {

    }


    /**
     * 格式化命令响应数据
     *
     * @param int        $type 表示数据类型，NIL类型不需要传入$value，ERROR和STATUS类型$value可选，INT、STRING、SET、MAP必选
     * @param mixed|null $value
     *
     * @return mixed
     */
    public static function format(int $type, mixed $value = null)
    {

    }
}
