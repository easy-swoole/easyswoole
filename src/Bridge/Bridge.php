<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Bridge\CommandHandel\Crontab;
use EasySwoole\EasySwoole\Bridge\CommandHandel\Process;
use EasySwoole\EasySwoole\Bridge\CommandHandel\Task;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Socket\Tools\Client;
use Swoole\Server;

class Bridge
{
    use Singleton;

    private $onStart;
    private $onCommand;

    protected $socketFile = EASYSWOOLE_TEMP_DIR . '/bridge.sock';


    function __construct()
    {
        $this->onCommand = new BridgeCommand();
    }

    function onCommand(): BridgeCommand
    {
        return $this->onCommand;
    }

    /**
     * send
     * @param Package $package
     * @param         $timeout
     * @return Package
     * @throws Exception
     * @author Tioncico
     * Time: 13:53
     */
    function send(Package $package, $timeout = 3.0): Package
    {
        $client = new Client(Bridge::getInstance()->getSocketFile());
        $client->send(serialize($package));
        $ret = $client->recv($timeout);
        $client->close();
        $package = unserialize($ret);
        if(!$package instanceof Package){
            $package = new Package();
            $package->setArgs('connect to server fail');
            $package->setStatus(Package::STATUS_UNIX_CONNECT_ERROR);
        }
        return $package;
    }

    function attachServer(Server $server)
    {
        $serverName = Config::getInstance()->getConf('SERVER_NAME');
        $config = new UnixProcessConfig();
        $config->setSocketFile($this->socketFile);
        $config->setProcessName("{$serverName}.Bridge");
        $config->setProcessGroup("{$serverName}.Bridge");
        $p = new BridgeProcess($config);
        $server->addProcess($p->getProcess());
    }

    /**
     * @return string
     */
    public function getSocketFile(): string
    {
        return $this->socketFile;
    }


}
