<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/29
 * Time: 上午11:46
 */

namespace EasySwoole\EasySwoole\Swoole\PipeMessage;

use EasySwoole\Component\Event;
use EasySwoole\Component\Singleton;

class OnCommand extends Event
{
    use Singleton;
}