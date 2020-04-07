<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Process\Manager;
use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\Socket\Tools\Protocol;
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
        $this->onCommand->set(BridgeCommand::PROCESS_INFO, function (Package $package) {
            $data = Manager::getInstance()->info();
            return $data;
        });
        $this->onCommand->set(BridgeCommand::SERVER_STATUS_INFO, function (Package $package) {
            $data = ServerManager::getInstance()->getSwooleServer()->stats();
            return $data;
        });
        $this->onCommand->set(BridgeCommand::TASK_INFO, function (Package $package) {
            $data = TaskManager::getInstance()->status();
            return $data;
        });
        $this->onCommand->set(BridgeCommand::CRON_INFO, function (Package $package) {
            $data = $this->getCrontabInfo();
            return $data;
        });
        $this->onCommand->set(BridgeCommand::CRON_STOP, function (Package $package) {
            $data = $this->crontabStop($package->getArgs());
            return $data;
        });
        $this->onCommand->set(BridgeCommand::CRON_RESUME, function (Package $package) {
            $data = $this->crontabResume($package->getArgs());
            return $data;
        });
    }

    function onCommand(): BridgeCommand
    {
        return $this->onCommand;
    }

    /**
     * send
     * @param Package $package
     * @return Package
     * @throws Exception
     * @author Tioncico
     * Time: 13:53
     */
    function send(Package $package): Package
    {
        $package = Protocol::unixSocketSendAndRecv(Bridge::getInstance()->getSocketFile(), $package);
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


    protected function getCrontabInfo()
    {
        $info = Crontab::getInstance()->infoTable();
        $data = [];
        foreach ($info as $k => $v) {
            $data[$k] = $v;
        }
        return $data;
    }

    protected function crontabStop($contabName)
    {
        $info = Crontab::getInstance()->infoTable();
        $crontab = $info->get($contabName);
        if (empty($crontab)) {
            return "crontab is not found.";
        }
        if ($crontab['isStop'] == 1) {
            return "crontab is already stop.";
        }

        $info->set($contabName, ['isStop' => 1]);
        return "crontab:test is stop suceess.";
    }

    protected function crontabResume($contabName)
    {
        $info = Crontab::getInstance()->infoTable();
        $crontab = $info->get($contabName);
        if (empty($crontab)) {
            return "crontab is not found.";
        }
        if ($crontab['isStop'] == 0) {
            return "crontab is running.";
        }
        $info->set($contabName, ['isStop' => 0]);
        return "crontab:test resume suceess.";
    }

    /**
     * @return string
     */
    public function getSocketFile(): string
    {
        return $this->socketFile;
    }

}
