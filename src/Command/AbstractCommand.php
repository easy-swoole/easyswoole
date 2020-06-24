<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\EasySwoole\Command;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Bridge\Bridge;
use Swoole\Coroutine\Scheduler;

abstract class AbstractCommand implements CommandInterface
{
    protected $helps = [];

    public function exec($args): ResultInterface
    {
        $result = new Result();
        $run = new Scheduler();
        $run->add(function () use (&$result, $args) {
            $action = array_shift($args);
            if (!method_exists($this, $action)) {
                $result = $this->help($args);
            } else {
                $result = $this->{$action}($args);
            }
        });
        $run->start();
        return $result;
    }

    public function help($args): ResultInterface
    {
        $helps = array_map(function ($help) {
            return 'php easyswoole ' . $help;
        }, $this->helps);

        $result = new Result();
        $msg = Utility::easySwooleLog() . implode(PHP_EOL, $helps);
        $result->setMsg($msg);
        return $result;
    }


    final protected function bridgeCall(callable $function, $action, $params = [], $timeout = 3)
    {
        $result = new Result();
        $arg = ['action' => $action] + $params;
        $package = Bridge::getInstance()->call($this->commandName(), $arg, $timeout);
        if ($package->getStatus() == Package::STATUS_SUCCESS) {
            call_user_func($function, $package, $result);
        } else {
            $result->setMsg($package->getMsg());
        }
        return $result;
    }
}