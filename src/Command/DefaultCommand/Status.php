<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use Swoole\Coroutine\Scheduler;

class Status implements CommandInterface
{
    public function commandName(): string
    {
        return "status";
    }

    public function exec($args): ResultInterface
    {
        $result = new Result();
        $run = new Scheduler();
        $run->add(function () use (&$result, $args) {
            $package = Bridge::getInstance()->call('status');
            if ($package->getStatus() == Package::STATUS_SUCCESS){
                $data = $package->getArgs();
                $data['start_time'] = date('Y-m-d H:i:s', $data['start_time']);
                $msg = '';
                foreach ($data as $key => $val) {
                    $msg .= Utility::displayItem($key, $val) . "\n";
                }
                $result->setMsg($msg);
            }else{
                $result->setMsg($package->getMsg());
            }
        });
        $run->start();
        return $result;
    }

    function help($args): ResultInterface
    {
        $result = new Result();
        $logo = Utility::easySwooleLog();
        $msg =  $logo . <<<HELP
php easyswoole status
HELP;
        $result->setMsg($msg);
        return $result;
    }
}
