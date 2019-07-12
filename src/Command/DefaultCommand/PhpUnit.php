<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Trigger;
use PHPUnit\TextUI\Command;
use Swoole\Coroutine\Scheduler;
use Swoole\ExitException;


class PhpUnit implements CommandInterface
{

    public function commandName(): string
    {
        return 'phpunit';
    }

    public function exec(array $args): ?string
    {
        /*
        * 清除输入变量
        */
        global $argv;
        array_shift($argv);
        $_SERVER['argv'] = $argv;
        /*
        * 允许自动的执行一些初始化操作，只初始化一次
        */
        if(file_exists(getcwd().'/phpunit.php')){
            require_once getcwd().'/phpunit.php';
        }
        $scheduler = new Scheduler();
        $scheduler->add(function() {
            try{
                Command::main(false);
            }catch (\Throwable $throwable){
                /*
                 * 屏蔽swoole exit报错
                 */
                if(!$throwable instanceof ExitException){
                    Trigger::getInstance()->throwable($throwable);
                }
            }
            Timer::getInstance()->clearAll();
        });
        $scheduler->start();
        return null;
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo.'php easyswoole phpunit testDir';
    }
}