<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\CommandManager;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

class Config implements CommandInterface
{
    public function commandName(): string
    {
        return 'config';
    }

    public function desc(): string
    {
        return 'config manager';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('show', 'show all configs');
        $commandHelp->addAction('set', 'set config');
        return $commandHelp;
    }

    public function exec(): string
    {
        $args = CommandManager::getInstance()->getArgs();
        $run = new Scheduler();
        $run->add(function () use (&$result, $args) {
            $action = array_shift($args);
            if (method_exists($this, $action)) {
                $result = $this->{$action}($args);
            } else {
                $result = '';
            }
        });
        $run->start();
        return $result;
    }

    protected function show($args)
    {
        $key = array_shift($args);
        return Utility::bridgeCall($this->commandName(), function (Package $package) {
            $data = $this->arrayConversion('', $package->getArgs());
            $data = $this->handelArray($data);
            return new ArrayToTextTable($data);
        }, 'info', ['key' => $key]);
    }

    protected function set($args)
    {
        $key = array_shift($args);
        $value = array_shift($args);
        return Utility::bridgeCall($this->commandName(), function (Package $package) {
            $data = $this->arrayConversion('', $package->getArgs());
            $data = $this->handelArray($data);
            return new ArrayToTextTable($data);
        }, 'set', ['key' => $key, 'value' => $value]);
    }

    protected function handelArray($array)
    {
        $temp = [];
        foreach ($array as $key => $value) {
            $temp[] = [
                'key' => $key,
                'value' => $value
            ];
        }
        return $temp;
    }

    protected function arrayConversion($key, $array)
    {
        $data = [];
        foreach ($array as $k => $value) {
            $keyName = empty($key) ? $k : "{$key}.{$k}";
            if (is_array($value)) {
                $data = array_merge($data, $this->arrayConversion($keyName, $value));
            } else {
                $data[$keyName] = $value;
            }
        }
        return $data;
    }
}
