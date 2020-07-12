<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:11
 */

namespace EasySwoole\EasySwoole\Command;


use EasySwoole\Command\AbstractInterface\CallerInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\Command\Result;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Command\DefaultCommand\Crontab;
use EasySwoole\EasySwoole\Command\DefaultCommand\Install;
use EasySwoole\EasySwoole\Command\DefaultCommand\PhpUnit;
use EasySwoole\EasySwoole\Command\DefaultCommand\Process;
use EasySwoole\EasySwoole\Command\DefaultCommand\Reload;
use EasySwoole\EasySwoole\Command\DefaultCommand\Restart;
use EasySwoole\EasySwoole\Command\DefaultCommand\Start;
use EasySwoole\EasySwoole\Command\DefaultCommand\Status;
use EasySwoole\EasySwoole\Command\DefaultCommand\Stop;
use EasySwoole\EasySwoole\Command\DefaultCommand\Task;
use EasySwoole\EasySwoole\Command\DefaultCommand\Config as ConfigCommand;
use EasySwoole\EasySwoole\Core;


class CommandRunner
{
    use Singleton;

    public function __construct()
    {
        CommandManager::getInstance()->addCommand(new Install());
        CommandManager::getInstance()->addCommand(new Start());
        CommandManager::getInstance()->addCommand(new Stop());
        CommandManager::getInstance()->addCommand(new Reload());
        CommandManager::getInstance()->addCommand(new Restart());
        CommandManager::getInstance()->addCommand(new PhpUnit());
        CommandManager::getInstance()->addCommand(new ConfigCommand());
        CommandManager::getInstance()->addCommand(new Task());
        CommandManager::getInstance()->addCommand(new Crontab());
        CommandManager::getInstance()->addCommand(new Process());
        CommandManager::getInstance()->addCommand(new Status());
    }

    private $beforeCommand;

    public function setBeforeCommand(callable $before)
    {
        $this->beforeCommand = $before;
    }

    public function run(CallerInterface $caller): ResultInterface
    {
        if (is_callable($this->beforeCommand)) {
            call_user_func($this->beforeCommand, $caller);
        }
        Utility::opCacheClear();
        $result = new Result();
        $msg = CommandManager::getInstance()->run($caller->getParams());
        $result->setMsg(Color::green(Utility::easySwooleLog()) . $msg);
        return $result;
    }
}
