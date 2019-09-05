<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Phpunit\Runner;
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
        $temp = $argv;
        array_shift($temp);
        $key = array_search('produce',$temp);
        if($key){
            unset($temp[$key]);
        }
        $_SERVER['argv'] = $temp;

        /*
        * 允许自动的执行一些初始化操作，只初始化一次
        */
        if(file_exists(getcwd().'/phpunit.php')){
            require_once getcwd().'/phpunit.php';
        }
        if(!class_exists(Runner::class)){
            return 'please require easyswoole/phpunit at first';
        }
        $scheduler = new Scheduler();
        $scheduler->add(function() {
            Runner::run();
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