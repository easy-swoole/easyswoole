<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/30
 * Time: 上午11:28
 */

namespace EasySwoole\Core\Component\Cluster\Event;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Container;

class MessageCallbackContainer extends Container
{
    use Singleton;
}