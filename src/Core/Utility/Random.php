<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 16/9/5
 * Time: 下午8:40
 */

namespace Core\Utility;


class Random
{
    static function randStr($length){
        return substr(str_shuffle("abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ23456789"),0,$length);
    }
    static function randNumStr($length){
        $chars = array(
            '0','1','2','3','4','5','6','7','8','9',
        );
        $password = '';
        while(strlen($password)<$length)
        {
            $password .= $chars[rand(0,9)];
        }
        return $password;
    }
}