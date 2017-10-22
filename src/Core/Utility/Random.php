<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 16/9/5
 * Time: 下午8:40
 */

namespace Core\Utility;

use Core\Swoole\Server;

class Random
{
    static function randStr($length, $unique = true)
    {
        $Alphabet = 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ123456789';
        if ($unique) {
            return substr(str_shuffle($Alphabet), 0, $length);
        } else {
            $randStr = '';
            for ($i = 0; $i < $length; $i++) {
                $randStr .= $Alphabet[mt_rand(0, 56)];
            }
            return $randStr;
        }
    }

    static function randNumStr($length)
    {
        $chars = array(
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        );
        $password = '';
        while (strlen($password) < $length) {
            $password .= $chars[rand(0, 9)];
        }
        return $password;
    }

    static function randOrderCode($prefix = '', $suffix = '', $EPOCH = 1479533469598)
    {
        $time = floor(microtime(true) * 1000);
        $time -= $EPOCH;
        $base = decbin(1099511627775 + $time);
        $workerID = Server::getInstance()->getServer()->worker_id;
        $machineId = str_pad(decbin($workerID), 10, "0", STR_PAD_LEFT);
        $random = str_pad(decbin(mt_rand(0, 4095)), 12, "0", STR_PAD_LEFT);
        $base = $base . $machineId . $random;
        return $prefix . bindec($base) . $suffix;
    }
}