<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/10/21
 * Time: 下午5:49
 */

namespace Core\Component\Socket\AbstractInterface;



use Core\Component\RPC\Client\Client;
use Core\Component\Socket\Common\Command;

abstract class AbstractCommandParser
{
    abstract function parser(Command $result,AbstractClient $client,$rawData);
}