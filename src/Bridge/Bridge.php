<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Bridge\Container;
use EasySwoole\Component\Singleton;
use EasySwoole\Bridge\Bridge as BridgeServer;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Crontab;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Process;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Status;

class Bridge extends BridgeServer
{
    use Singleton;

    function __construct(Container $container = null)
    {
        parent::__construct($container);
        $this->getCommandContainer()->set(new Crontab());
        $this->getCommandContainer()->set(new Process());
        $this->getCommandContainer()->set(new Status());
        $this->setSocketFile(EASYSWOOLE_TEMP_DIR . '/bridge.sock');
    }
}
