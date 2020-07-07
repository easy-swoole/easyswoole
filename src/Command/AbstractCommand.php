<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\EasySwoole\Command;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CallerInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Bridge\Bridge;
use Swoole\Coroutine\Scheduler;

abstract class AbstractCommand implements CommandInterface
{
    protected $helps = [];

    public function exec(CallerInterface $caller): ResultInterface
    {
        $run = new Scheduler();
        $run->add(function () use (&$result, $caller) {
            $args = $caller->getParams();
            reset($args);
            $action = key($args);
            if (!method_exists($this, $action)) {
                $result = $this->help($caller);
            } else {
                unset($args[$action]);
                $caller->setParams($args);
                $result = $this->{$action}($caller);
            }
        });
        $run->start();
        return $result;
    }

    public function help(CallerInterface $caller): ResultInterface
    {
        $helps = array_map(function ($help) {
            return 'php easyswoole ' . $help;
        }, $this->helps);

        $result = new Result();
        $msg    = Utility::easySwooleLog() . implode(PHP_EOL, $helps);
        $result->setMsg($msg);
        return $result;
    }


    final protected function bridgeCall(callable $function, $action, $params = [], $timeout = 3): ResultInterface
    {
        $result  = new Result();
        $arg     = ['action' => $action] + $params;
        $package = Bridge::getInstance()->call($this->commandName(), $arg, $timeout);
        if ($package->getStatus() == Package::STATUS_SUCCESS) {
            call_user_func($function, $package, $result);
        } else {
            $result->setMsg($package->getMsg());
        }
        return $result;
    }
}