<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/8
 * Time: 上午2:04
 */

namespace EasySwoole\Core\Utility;


class Random
{
    static function randStr($length)
    {
        return substr(str_shuffle("abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ23456789"), 0, $length);
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
}