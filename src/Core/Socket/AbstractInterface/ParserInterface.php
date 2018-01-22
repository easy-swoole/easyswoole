<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/22
 * Time: 下午1:00
 */

namespace EasySwoole\Core\Socket\AbstractInterface;


use EasySwoole\Core\Socket\Command\CommandBean;

interface ParserInterface
{
    public function decode($raw,array $args):?CommandBean;
    public function encode(?string $raw,array $args):?string ;
}