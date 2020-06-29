<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\EasySwoole\Command\Utility;
use Swoole\Coroutine\Scheduler;

class Status extends AbstractCommand
{
    protected $helps = [
        'status'
    ];

    public function commandName(): string
    {
        return "status";
    }

    public function exec($args): ResultInterface
    {
        $run = new Scheduler();
        $run->add(function () use (&$result, $args) {
            $result = $this->bridgeCall(function (Package $package, Result $result) {
                $data = $package->getArgs();
                $data['start_time'] = date('Y-m-d H:i:s', $data['start_time']);
                $msg = '';
                foreach ($data as $key => $val) {
                    $msg .= Utility::displayItem($key, $val) . "\n";
                }
                $result->setMsg($msg);
            }, 'info');
        });
        $run->start();
        return $result;
    }
}
