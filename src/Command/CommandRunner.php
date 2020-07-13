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
use EasySwoole\EasySwoole\Command\DefaultCommand\Server;
use EasySwoole\EasySwoole\Command\DefaultCommand\Task;
use EasySwoole\EasySwoole\Command\DefaultCommand\Config as ConfigCommand;


class CommandRunner
{
    use Singleton;

    public function __construct()
    {
        CommandManager::getInstance()->addCommand(new Install());
        CommandManager::getInstance()->addCommand(new PhpUnit());
        CommandManager::getInstance()->addCommand(new ConfigCommand());
        CommandManager::getInstance()->addCommand(new Task());
        CommandManager::getInstance()->addCommand(new Crontab());
        CommandManager::getInstance()->addCommand(new Process());
        CommandManager::getInstance()->addCommand(new Server());
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
