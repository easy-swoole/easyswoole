<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/30
 * Time: 上午11:06
 */

namespace EasySwoole\Core\Component\Cluster\Event;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Container;


class ShutdownCallBackContainer extends Container
{
    use Singleton;
}