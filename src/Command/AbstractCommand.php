<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\EasySwoole\Command;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\CommandManager;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Bridge\Bridge;
use Swoole\Coroutine\Scheduler;

abstract class AbstractCommand implements CommandInterface
{
    public function exec(): string
    {
        $args = CommandManager::getInstance()->getArgs();
        $run = new Scheduler();
        $run->add(function () use (&$result, $args) {
            $action = array_shift($args);
            if (!method_exists($this, $action)) {
                $result = "The action '{$action}' is not exists!";
            } else {
                $result = $this->{$action}($args);
            }
        });
        $run->start();
        return $result;
    }

    public function help(): array
    {
        return [];
    }

    public function desc(): string
    {
        return '';
    }

    final protected function bridgeCall(callable $function, $action, $params = [], $timeout = 3)
    {
        $arg = ['action' => $action] + $params;
        $package = Bridge::getInstance()->call($this->commandName(), $arg, $timeout);
        if ($package->getStatus() == Package::STATUS_SUCCESS) {
            $result = call_user_func($function, $package);
        } else {
            $result = $package->getMsg();
        }
        return $result;
    }
}