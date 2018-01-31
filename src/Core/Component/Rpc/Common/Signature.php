<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/31
 * Time: 下午3:22
 */

namespace EasySwoole\Core\Component\Rpc\Common;


use EasySwoole\Core\Component\Rpc\Server\ServiceNode;
use EasySwoole\Core\Socket\Common\CommandBean;

class Signature
{
    static function signature()
    {

    }

    static function check(CommandBean $bean,ServiceNode $node)
    {
        $time = time();
    }
}