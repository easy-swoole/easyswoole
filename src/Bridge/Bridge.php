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
        \EasySwoole\EasySwoole\Bridge\CommandHandel\Server::initCommand($this->onCommand);
        Crontab::initCommand($this->onCommand);
        Process::initCommand($this->onCommand);
        Task::initCommand($this->onCommand);
        \EasySwoole\EasySwoole\Bridge\CommandHandel\Config::initCommand($this->onCommand);
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
        if (empty($ret)) {
            throw new Exception("connect server error");
        }
        $package = unserialize($ret);
        /**
         * @var  $package Package
         */
        if ($package->getStatus() == $package::STATUS_COMMAND_ERROR) {
            throw new Exception("command package error");
        }
        if ($package->getStatus() == $package::STATUS_PACKAGE_ERROR) {
            throw new Exception("command package error");
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

//    protected

    /**
     * @return string
     */
    public function getSocketFile(): string
    {
        return $this->socketFile;
    }


}
