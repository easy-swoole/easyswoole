<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CallerInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;
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

    protected function stop(CallerInterface $caller)
    {
        $taskName = key($caller->getOneParam());
        return $this->bridgeCall(function (Package $package, Result $result) {
            $data = $package->getMsg();
            $result->setMsg($data . PHP_EOL . $this->show()->getMsg());
        }, 'stop', ['taskName' => $taskName]);
    }


    protected function resume(CallerInterface $caller)
    {
        $taskName = key($caller->getOneParam());
        return $this->bridgeCall(function (Package $package, Result $result) {
            $data = $package->getMsg();
            $result->setMsg($data . PHP_EOL . $this->show()->getMsg());
        }, 'resume', ['taskName' => $taskName]);
    }

    protected function run(CallerInterface $caller)
    {
        $taskName = key($caller->getOneParam());
        return $this->bridgeCall(function (Package $package, Result $result) {
            $data = $package->getMsg();
            $result->setMsg($data . PHP_EOL . $this->show()->getMsg());
        }, 'run', ['taskName' => $taskName]);
    }

    protected function show()
    {
        return $this->bridgeCall(function (Package $package, Result $result) {
            $data = $package->getArgs();
            foreach ($data as $k => $v) {
                $v['taskNextRunTime'] = date('Y-m-d H:i:s', $v['taskNextRunTime']);
                $data[$k] = array_merge(['taskName' => $k], $v);
            }
            $result->setMsg(new ArrayToTextTable($data));
        }, 'show');
    }

}
