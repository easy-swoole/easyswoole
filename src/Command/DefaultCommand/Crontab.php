<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use Swoole\Coroutine\Scheduler;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;

class Crontab implements CommandInterface
{
    public function commandName(): string
    {
        return 'crontab';
    }

    public function exec($args): ResultInterface
    {
        $result = new Result();
        $run = new Scheduler();
        $run->add(function () use (&$result, $args) {
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
        });
        $run->start();
        return $result;
    }

    protected function stop($args)
    {
        $result = new Result();
        $taskName = array_shift($args);

        $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'stop', 'taskName' => $taskName], 3);
        if ($package->getStatus() == Package::STATUS_SUCCESS) {
            $data = $package->getMsg();
            $result->setMsg($data.PHP_EOL . $this->show()->getMsg());
        } else {
            $result->setMsg($package->getMsg());
        }
        return $result;
    }


    protected function resume($args)
    {
        $result = new Result();
        $taskName = array_shift($args);

        $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'resume', 'taskName' => $taskName], 3);
        if ($package->getStatus() == Package::STATUS_SUCCESS) {
            $data = $package->getMsg();
            $result->setMsg($data.PHP_EOL . $this->show()->getMsg());
        } else {
            $result->setMsg($package->getMsg());
        }
        return $result;
    }

    protected function run($args)
    {
        $result = new Result();
        $taskName = array_shift($args);

        $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'run', 'taskName' => $taskName], 3);
        if ($package->getStatus() == Package::STATUS_SUCCESS) {
            $data = $package->getMsg();
            $result->setMsg($data.PHP_EOL . $this->show()->getMsg());
        } else {
            $result->setMsg($package->getMsg());
        }
        return $result;
    }

    protected function show()
    {
        $result = new Result();
        $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'show'], 3);
        if ($package->getStatus() == Package::STATUS_SUCCESS) {
            $data = $package->getArgs();
            foreach ($data as $k => $v) {
                $v['taskNextRunTime'] = date('Y-m-d H:i:s', $v['taskNextRunTime']);
                $data[$k] = array_merge(['taskName' => $k], $v);
            }
            $result->setMsg(new ArrayToTextTable($data));
        } else {
            $result->setMsg($package->getMsg());
        }
        return $result;
    }


    public function help($args): ResultInterface
    {
        $result = new Result();
        $logo = Utility::easySwooleLog();
        $msg = $logo . "
php easyswoole crontab show
php easyswoole crontab stop taskName
php easyswoole crontab resume taskName 
php easyswoole crontab run taskName 
";
        $result->setMsg($msg);
        return $result;
    }

}
