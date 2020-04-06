<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use Swoole\Server;

class Bridge
{
    use Singleton;


    private $onStart;
    private $onCommand;

    function __construct()
    {
        $this->onCommand = new BridgeCommand();
        $this->onCommand->add(BridgeCommand::PROCESS_INFO,function (){

        });
    }

    function onCommand():BridgeCommand
    {
        return $this->onCommand;
    }

    function send(Bridge $bridge):Package
    {

    }

    function attachServer(Server $server)
    {
        $serverName = Config::getInstance()->getConf('SERVER_NAME');
        $config = new UnixProcessConfig();
        $config->setSocketFile(EASYSWOOLE_TEMP_DIR.'/bridge.sock');
        $config->setProcessName("{$serverName}.Bridge");
        $config->setProcessGroup("{$serverName}.Bridge");
        $p = new BridgeProcess($config);
        $server->addProcess($p->getProcess());
    }

}