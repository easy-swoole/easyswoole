<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/30
 * Time: 下午5:44
 */

namespace EasySwoole\Core\Component\Cluster\Common;

use EasySwoole\Core\Component\Event;


class EventRegister extends Event
{
    function __construct(array $allowKeys = null)
    {
        parent::__construct(['CLUSTER_START', 'CLUSTER_SHUTDOWN', 'CLUSTER_ON_COMMAND']);
    }
}