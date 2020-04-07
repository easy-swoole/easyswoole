<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:11
 */

namespace EasySwoole\EasySwoole\Command;


use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Command\DefaultCommand\Crontab;
use EasySwoole\EasySwoole\Command\DefaultCommand\Help;
use EasySwoole\EasySwoole\Command\DefaultCommand\Install;
use EasySwoole\EasySwoole\Command\DefaultCommand\PhpUnit;
use EasySwoole\EasySwoole\Command\DefaultCommand\Process;
use EasySwoole\EasySwoole\Command\DefaultCommand\Reload;
use EasySwoole\EasySwoole\Command\DefaultCommand\Restart;
use EasySwoole\EasySwoole\Command\DefaultCommand\Start;
use EasySwoole\EasySwoole\Command\DefaultCommand\Status;
use EasySwoole\EasySwoole\Command\DefaultCommand\Stop;
use EasySwoole\EasySwoole\Command\DefaultCommand\Config;
use EasySwoole\EasySwoole\Command\DefaultCommand\Task;
use EasySwoole\EasySwoole\Core;

class CommandRunner
{
    use Singleton;

    function __construct()
    {
        CommandContainer::getInstance()->set(new Help());
        CommandContainer::getInstance()->set(new Install());
        CommandContainer::getInstance()->set(new Start());
        CommandContainer::getInstance()->set(new Stop());
        CommandContainer::getInstance()->set(new Reload());
        CommandContainer::getInstance()->set(new PhpUnit());
        CommandContainer::getInstance()->set(new Restart());
        CommandContainer::getInstance()->set(new Config());
        CommandContainer::getInstance()->set(new Process());
        CommandContainer::getInstance()->set(new Status());
        CommandContainer::getInstance()->set(new Task());
        CommandContainer::getInstance()->set(new Crontab());
    }

    function run(array $args):?string
    {
        $command = array_shift($args);
        if(empty($command)){
            $command = 'help';
        }else if($command != 'install'){
            //预先加载配置
            if(in_array('produce',$args)){
                Core::getInstance()->setIsDev(false);
            }
            Core::getInstance()->initialize();
        }
        if(!CommandContainer::getInstance()->get($command)){
            $command = 'help';
        }
        return CommandContainer::getInstance()->hook($command,$args);
    }
}