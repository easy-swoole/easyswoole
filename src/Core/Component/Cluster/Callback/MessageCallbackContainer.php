<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/30
 * Time: 上午11:28
 */

namespace EasySwoole\Core\Component\Cluster\Callback;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Event;


class MessageCallbackContainer extends Event
{
    use Singleton;
}