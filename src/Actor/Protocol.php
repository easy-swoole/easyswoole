<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/11
 * Time: 1:32 PM
 */

namespace EasySwoole\EasySwoole\Actor;


class Protocol
{
    public static function pack(string $data): string
    {
        return pack('N', strlen($data)).$data;
    }

    public static function packDataLength(string $head): int
    {
        return unpack('N', $head)[1];
    }

    public static function unpack(string $data):string
    {
        return substr($data,4);
    }
}