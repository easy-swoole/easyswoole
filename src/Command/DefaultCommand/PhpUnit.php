<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\CallerInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\Phpunit\Runner;


class PhpUnit extends AbstractCommand
{
    protected $helps = [
        'phpunit testDir',
        'restart testDir [--no-coroutine]',
        'restart testDir [produce]',
        'restart testDir [produce] [--no-coroutine]'
    ];

    public function commandName(): string
    {
        return 'phpunit';
    }

    public function exec(CallerInterface $caller): ResultInterface
    {
        /*
         * 允许自动的执行一些初始化操作，只初始化一次
        */
        if (file_exists(getcwd() . '/phpunit.php')) {
            require_once getcwd() . '/phpunit.php';
        }
        /*
        * 清除输入变量
        */
        global $argv;
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
        return new Result();
    }

}