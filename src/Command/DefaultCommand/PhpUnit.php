<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\CommandManager;
use EasySwoole\Phpunit\Runner;


class PhpUnit implements CommandInterface
{
    public function commandName(): string
    {
        return 'phpunit';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('--no-coroutine', 'close coroutine');
        return $commandHelp;
    }

    public function desc(): string
    {
        return 'Unit testing';
    }

    public function exec(): ?string
    {
        /*
         * 允许自动的执行一些初始化操作，只初始化一次
        */
        if (file_exists(getcwd() . '/phpunit.php')) {
            require_once getcwd() . '/phpunit.php';
        }

        $argv = CommandManager::getInstance()->getOriginArgv();

        // remove phpunit
        array_shift($argv);

        $key = array_search('--no-coroutine', $argv);

        if ($key !== false) {
            $noCoroutine = true;
            unset($argv[$key]);
        } else {
            $noCoroutine = false;
        }

        $_SERVER['argv'] = $argv;
        if (!class_exists(Runner::class)) {
            echo "please require easyswoole/phpunit at first \n";
        }
        Runner::run($noCoroutine);
        return null;
    }

}