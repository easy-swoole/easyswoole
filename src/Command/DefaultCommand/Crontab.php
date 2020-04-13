<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use Swoole\Coroutine\Scheduler;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Bridge\BridgeCommand;
use EasySwoole\EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;

class Crontab implements CommandInterface
{
    public function commandName(): string
    {
        return 'crontab';
    }

    public function exec(array $args): ?string
    {
        $ret = '';
        $run = new Scheduler();
        $run->add(function () use (&$ret, $args) {
            $action = array_shift($args);
            switch ($action) {
                case 'show':
                    $result = $this->show();
                    break;
                case 'stop':
                    $result = $this->stop($args);
                    break;
                case 'resume':
                    $result = $this->resume($args);
                    break;
                case 'run':
                    $result = $this->run($args);
                    break;
                default:
                    $result = $this->help($args);
                    break;
            }
            $ret = $result;
        });
        $run->start();
        return $ret;
    }

    protected function stop($args)
    {
        $taskName = array_shift($args);

        $package = new Package();
        $package->setCommand(BridgeCommand::CRON_STOP);
        $package->setArgs($taskName);
        $package = Bridge::getInstance()->send($package);
        if($package->getStatus() !== Package::STATUS_SUCCESS){
            return $package->getArgs();
        }
        if (empty($package->getArgs())) {
            return "stop error.";
        }
        $data = $package->getArgs();
        $data .= "\n" . $this->show();
        return $data;
    }


    protected function resume($args)
    {
        $taskName = array_shift($args);
        $package = new Package();
        $package->setCommand(BridgeCommand::CRON_RESUME);
        $package->setArgs($taskName);
        $package = Bridge::getInstance()->send($package);
        if($package->getStatus() !== Package::STATUS_SUCCESS){
            return $package->getArgs();
        }
        if (empty($package->getArgs())) {
            return "resume error";
        }
        $data = $package->getArgs();
        $data .= "\n" . $this->show();
        return $data;
    }

    protected function run($args)
    {
        $taskName = array_shift($args);
        $package = new Package();
        $package->setCommand(BridgeCommand::CRON_RUN);
        $package->setArgs($taskName);
        $package = Bridge::getInstance()->send($package);
        if($package->getStatus() !== Package::STATUS_SUCCESS){
            return $package->getArgs();
        }
        if (empty($package->getArgs())) {
            return "run error";
        }
        $data = $package->getArgs();
        $data .= "\n" . $this->show();
        return $data;
    }

    protected function show()
    {
        $package = new Package();
        $package->setCommand(BridgeCommand::CRON_INFO);
        $package = Bridge::getInstance()->send($package);
        if($package->getStatus() !== Package::STATUS_SUCCESS){
            return $package->getArgs();
        }
        if (empty($package->getArgs())) {
            return "crontab info is abnormal";
        }
        $data = $package->getArgs();

        foreach ($data as $k => $v) {
            $v['taskNextRunTime'] = date('Y-m-d H:i:s', $v['taskNextRunTime']);
            $data[$k] = array_merge(['taskName' => $k], $v);
        }
        return new ArrayToTextTable($data);
    }


    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo . "
php easyswoole crontab show
php easyswoole crontab stop taskName
php easyswoole crontab resume taskName 
php easyswoole crontab run taskName 
";
    }

}
