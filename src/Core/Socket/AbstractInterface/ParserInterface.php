<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/22
 * Time: 下午1:00
 */

namespace EasySwoole\Core\Socket\AbstractInterface;


use EasySwoole\Core\Socket\Common\CommandBean;

interface ParserInterface
{
    public function decode($raw,array $args):?CommandBean;
    public function encode(?CommandBean $raw,array $args):?string ;
}