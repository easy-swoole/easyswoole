<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\CommandManager;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\Phpunit\Runner;


class PhpUnit extends AbstractCommand
{
    public function commandName(): string
    {
        return 'phpunit';
    }

    public function help(): array
    {
        return [
            'testDir',
            'testDir [--no-coroutine]',
            'testDir [produce]',
            'testDir [produce] [--no-coroutine]',
        ];
    }

    public function desc(): string
    {
        return '单元测试';
    }

    public function exec(): string
    {
        /*
         * 允许自动的执行一些初始化操作，只初始化一次
        */
        if (file_exists(getcwd() . '/phpunit.php')) {
            require_once getcwd() . '/phpunit.php';
        }

        $argv = CommandManager::getInstance()->getOriginArgv();
        array_shift($argv);
        $key = array_search('produce', $argv);
        if ($key) {
            unset($argv[$key]);
        }
        $key = array_search('--no-coroutine', $argv);
        if ($key) {
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
        exit();
    }

}