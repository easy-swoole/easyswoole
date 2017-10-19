<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/19
 * Time: 下午5:52
 */

namespace Core\Component\RPC\Common;


use Core\Component\Socket\Client\TcpClient;

abstract class AbstractPackageDecoder
{
    abstract function decode($rawData);
}