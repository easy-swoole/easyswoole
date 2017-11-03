<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/23
 * Time: 下午4:15
 */

namespace Core\Component\RPC\Common;


use Core\Component\RPC\AbstractInterface\AbstractPackageParser;
use Core\Component\Socket\Client\TcpClient;

class DefaultPackageParser extends AbstractPackageParser
{
    function decode(Package $result, TcpClient $client, $rawData)
    {
        // TODO: Implement decode() method.
        $rawData = pack('H*', base_convert($rawData, 2, 16));
        $js = json_decode($rawData,1);
        $js = is_array($js) ? $js :[];
        $result->arrayToBean($js);
    }

    function encode(Package $res)
    {
        // TODO: Implement encode() method.
        $data = $res->__toString();
        $value = unpack('H*', $data);
        return base_convert($value[1], 16, 2);
    }

}