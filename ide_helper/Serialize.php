<?php

namespace Swoole;

/**
 * swoole的二进制序列化库
 * 序列化后的结果为二进制格式，只适合机器读取，不适合人读
 * 序列化性能更高，可节省大量CPU资源，基准测试中序列化和反序列化耗时为PHP官方serialize的40%
 * 序列化后的结果数据尺寸更小，可节省内存资源，基准测试中序列化结果尺寸为PHP官方serialize的50%
 *
 * serialize模块仅在PHP7或更高版本中可用
 *
 * @package Swoole
 */
class Serialize {
    /**
     * 将PHP变量序列化
     *
     * @param mixed $data  要进行序列化的变量，请注意function和resource类型的变量是不支持序列化的
     * @param int   $flags 是否启用快速模式，swoole_serialize默认会使用静态表保存关联数组的Key，设置此参数为SWOOLE_FAST_PACK后将不再保存数组key
     *
     * @return string|bool 序列化成功后返回二进制字符串，失败返回false
     */
    public static function pack($data, $flags = 0) { }

    /**
     * @param string $data 序列化数据，必须是由swoole_serialize::pack函数生成
     *
     * @return mixed 操作成功返回PHP变量，失败返回false
     */
    public static function unpack($data) { }
}