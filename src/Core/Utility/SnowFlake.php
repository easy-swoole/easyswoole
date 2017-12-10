<?php
/**
 * Created by PhpStorm.
 * User: eValor
 * Date: 2017/12/10
 * Time: 上午11:05
 */

namespace EasySwoole\Core\Utility;

/**
 * 雪花算法生成器
 * Class SnowFlake
 * @author : evalor <master@evalor.cn>
 * @package EasySwoole\Core\Utility
 */
class SnowFlake
{
    private static $lastTimestamp = 0;
    private static $lastSequence  = 0;
    private static $sequenceMask  = 4095;
    private static $twepoch       = 1508945092000;

    /**
     * 生成基于雪花算法的随机编号
     * @author : evalor <master@evalor.cn>
     * @param int $dataCenterID 数据中心ID 0-31
     * @param int $workerID 任务进程ID 0-31
     * @return int 分布式ID
     */
    static function make($dataCenterID = 0, $workerID = 0)
    {
        // 41bit timestamp + 5bit dataCenter + 5bit worker + 12bit

        $timestamp = self::timeGen();

        if (self::$lastTimestamp == $timestamp) {
            self::$lastSequence = (self::$lastSequence + 1) & self::$sequenceMask;
            if (self::$lastSequence == 0) $timestamp = self::tilNextMillis(self::$lastTimestamp);
        } else {
            self::$lastSequence = 0;
        }
        self::$lastTimestamp = $timestamp;

        $snowFlakeId = (($timestamp - self::$twepoch) << 22) | ($dataCenterID << 17) | ($workerID << 12) | self::$lastSequence;
        return $snowFlakeId;
    }

    /**
     * 反向解析雪花算法生成的编号
     * @author : evalor <master@evalor.cn>
     * @param int|float $snowFlakeId
     * @return \stdClass
     */
    static function unmake($snowFlakeId)
    {
        $Binary               = str_pad(decbin($snowFlakeId), 64, '0', STR_PAD_LEFT);
        $Object               = new \stdClass;
        $Object->timestamp    = bindec(substr($Binary, 0, 41)) + self::$twepoch;
        $Object->dataCenterID = bindec(substr($Binary, 42, 5));
        $Object->workerID     = bindec(substr($Binary, 47, 5));
        $Object->sequence     = bindec(substr($Binary, -12));
        return $Object;
    }

    /**
     * 等待下一毫秒的时间戳
     * @author : evalor <master@evalor.cn>
     * @param $lastTimestamp
     * @return float
     */
    private static function tilNextMillis($lastTimestamp)
    {
        $timestamp = self::timeGen();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = self::timeGen();
        }
        return $timestamp;
    }

    /**
     * 获取毫秒级时间戳
     * @author : evalor <master@evalor.cn>
     * @return float
     */
    private static function timeGen()
    {
        return (float)sprintf('%.0f', microtime(true) * 1000);
    }
}