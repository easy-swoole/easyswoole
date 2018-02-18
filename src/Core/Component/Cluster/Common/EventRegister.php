<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/30
 * Time: 下午5:44
 */

namespace EasySwoole\Core\Component\Cluster\Common;

use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Cluster\Communicate\CommandBean;
use EasySwoole\Core\Component\Event;


class EventRegister extends Event
{
    use Singleton;

    const CLUSTER_START = 'CLUSTER_START';
    const CLUSTER_SHUTDOWN = 'CLUSTER_SHUTDOWN';
    const CLUSTER_ON_COMMAND = 'CLUSTER_ON_COMMAND';

    function __construct(array $allowKeys = null)
    {
        parent::__construct(['CLUSTER_START', 'CLUSTER_SHUTDOWN', 'CLUSTER_ON_COMMAND']);
        //注册默认命令处理
        $this->set(self::CLUSTER_ON_COMMAND,function (CommandBean $commandBean,...$args){
            CommandRegister::getInstance()->hook($commandBean->getCommand(),$commandBean,...$args);
        });
    }
}