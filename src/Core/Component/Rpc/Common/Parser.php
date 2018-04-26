<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/22
 * Time: 下午2:00
 */

namespace EasySwoole\Core\Component\Rpc\Common;
use EasySwoole\Core\Socket\AbstractInterface\ParserInterface;

class Parser implements ParserInterface
{
    public static function decode($raw, $client):?ServiceCaller
    {
        // TODO: Implement decode() method.
        $arr = json_decode($raw,true);
        if(is_array($arr)){
            return new ServiceCaller($arr);
        }else{
            return null;
        }
    }

    public static function encode(string $raw, $client, $commandBean): ?string
    {
        // TODO: Implement encode() method.

    }

    public static function unPack($raw)
    {
        return substr($raw, 4);
    }
    public static function pack($sendStr)
    {
        return pack('N', strlen($sendStr)).$sendStr;
    }


}