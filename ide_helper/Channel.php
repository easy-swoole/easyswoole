<?php
namespace Swoole;

/**
 * Class swoole_channel
 */
class Channel
{

    /**
     * @param int $size 通道占用的内存的尺寸，单位为字节。最小值为64K，最大值没有限制
     * 系统内存不足底层会抛出内存不足异常
     */
    public function __construct($size)
    {
    }


    /**
     * 向通道写入数据
     * $data 可以为任意PHP变量，当$data是非字符串类型时底层会自动进行串化
     * $data的尺寸超过8K时会启用临时文件存储数据
     * $data必须为非空变量，如空字符串、空数组、0、null、false
     * 写入成功返回true
     * 通道的空间不足时写入失败并返回false
     *
     */
    public function push($data)
    {
    }


    /**
     * 弹出数据
     * 无需传入任何参数
     * 当通道内有数据时自动将数据弹出并还原为PHP变量
     * 当通道内没有任何数据时pop会失败并返回false
     *
     */
    public function pop()
    {
    }


    /**
     * 获取通道的状态
     * 返回一个数组，包括2项信息
     * queue_num 通道中的元素数量
     * queue_bytes 通道当前占用的内存字节数
     */
    public function stats()
    {
    }
}

