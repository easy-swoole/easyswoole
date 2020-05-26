<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Phpunit\Runner;


class PhpUnit implements CommandInterface
{
    public function commandName(): string
    {
        return 'phpunit';
    }

    public function exec($args): ResultInterface
    {
        /*
         * 允许自动的执行一些初始化操作，只初始化一次
        */
        if(file_exists(getcwd().'/phpunit.php')){
            require_once getcwd().'/phpunit.php';
        }
        /*
        * 清除输入变量
        */
        global $argv;
        array_shift($argv);
        $key = array_search('produce',$argv);
        if($key){
            unset($argv[$key]);
        }
        $key = array_search('--no-coroutine',$argv);
        if($key){
            $noCoroutine = true;
            unset($argv[$key]);
        }else{
            $noCoroutine = false;
        }
        $_SERVER['argv'] = $argv;
        if(!class_exists(Runner::class)){
            echo "please require easyswoole/phpunit at first \n";
        }
        Runner::run($noCoroutine);
        return new Result();
    }

    public function help($args): ResultInterface
    {
        $result = new Result();
        $msg = Utility::easySwooleLog().<<<HELP_START
php easyswoole phpunit testDir  
php easyswoole restart testDir [--no-coroutine]
php easyswoole restart testDir [produce]
php easyswoole restart testDir [produce] [--no-coroutine]
HELP_START;
        $result->setMsg($msg);
        return  $result;
    }

}