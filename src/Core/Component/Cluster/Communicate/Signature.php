<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/31
 * Time: 下午1:49
 */

namespace EasySwoole\Core\Component\Cluster\Communicate;


class Signature
{
    static function sign(CommandBean &$bean):CommandBean
    {

    }

    static function check(CommandBean $bean, $ttl = 10):bool
    {

    }
}