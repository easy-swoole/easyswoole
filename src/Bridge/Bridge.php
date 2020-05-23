<?php


namespace EasySwoole\EasySwoole\Bridge;



use EasySwoole\Bridge\Container;
use EasySwoole\Component\Singleton;
use EasySwoole\Bridge\Bridge as BridgeServer;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Task;

class Bridge extends BridgeServer
{
    use Singleton;

    function __construct(Container $container = null)
    {
        parent::__construct($container);
        $this->getCommandContainer()->set(new Task());
    }
}
