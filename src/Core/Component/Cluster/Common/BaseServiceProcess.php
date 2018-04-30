<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/30
 * Time: 上午10:34
 */

namespace EasySwoole\Core\Component\Cluster\Common;


use EasySwoole\Core\Component\Cluster\Callback\BroadcastCallbackContainer;
use EasySwoole\Core\Component\Cluster\Callback\ShutdownCallbackContainer;
use EasySwoole\Core\Swoole\Process\AbstractProcess;
use Swoole\Process;

class BaseServiceProcess extends AbstractProcess
{

    public function run(Process $process)
    {
        // TODO: Implement run() method.
        $this->addTick($this->getArg('currentNode')->getBroadcastTTL()*1000,function (){
            BroadcastCallbackContainer::getInstance()->call();
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
        ShutdownCallbackContainer::getInstance()->call();
    }

    public function onReceive(string $str, ...$args)
    {
        // TODO: Implement onReceive() method.
    }
}