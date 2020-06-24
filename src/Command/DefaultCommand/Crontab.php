<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\Utility\ArrayToTextTable;

class Crontab extends AbstractCommand
{
    protected $helps = [
        'crontab show',
        'stop taskName',
        'resume taskName',
        'run taskName'
    ];

    public function commandName(): string
    {
        return 'crontab';
    }

    protected function stop($args)
    {
        $result = new Result();
        $taskName = array_shift($args);

        $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'stop', 'taskName' => $taskName], 3);
        if ($package->getStatus() == Package::STATUS_SUCCESS) {
            $data = $package->getMsg();
            $result->setMsg($data . PHP_EOL . $this->show()->getMsg());
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
            $result->setMsg($data . PHP_EOL . $this->show()->getMsg());
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
            $result->setMsg($data . PHP_EOL . $this->show()->getMsg());
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

}
