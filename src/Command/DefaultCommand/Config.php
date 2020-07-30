<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Core;
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
        return 'Config manager';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('show', 'show all configs');
        $commandHelp->addAction('set', 'set config');
        $commandHelp->addActionOpt('--appoint=CONFIG_KEY', 'display the specified key, example --appoint=LOG_DIR');
        $commandHelp->addActionOpt('--CONFIG_KEY=CONFIG_VALUE', 'key value pair[...key=value] example --title=easywoole');
        return $commandHelp;
    }

    public function exec(): ?string
    {
        $action = CommandManager::getInstance()->getArg(0);
        $run = new Scheduler();
        $run->add(function () use (&$result, $action) {
            if (method_exists($this, $action)) {
                Core::getInstance()->initialize();
                $result = $this->{$action}();
                return;
            }

            $result = CommandManager::getInstance()->displayCommandHelp($this->commandName());
        });
        $run->start();
        return $result;
    }

    protected function show()
    {
        $key = CommandManager::getInstance()->getOpt('appoint');
        return Utility::bridgeCall($this->commandName(), function (Package $package) {
            $data = $this->arrayConversion('', $package->getArgs());
            $data = $this->handelArray($data);
            return new ArrayToTextTable($data);
        }, 'info', ['key' => $key]);
    }

    protected function set()
    {
        $data = CommandManager::getInstance()->getOpts();

        $result = '';

        foreach ($data as $key => $value) {
            $result .= Utility::bridgeCall($this->commandName(), function (Package $package) {
                $data = $this->arrayConversion('', $package->getArgs());
                $data = $this->handelArray($data);
                return new ArrayToTextTable($data);
            }, 'set', ['key' => $key, 'value' => $value]);
        }
        return $result;
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
