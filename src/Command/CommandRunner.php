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
use EasySwoole\Command\CommandManager;
use EasySwoole\Command\Result;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Command\DefaultCommand\Crontab;
use EasySwoole\EasySwoole\Command\DefaultCommand\Install;
use EasySwoole\EasySwoole\Command\DefaultCommand\Process;
use EasySwoole\EasySwoole\Command\DefaultCommand\Server;
use EasySwoole\EasySwoole\Command\DefaultCommand\Task;
use EasySwoole\HttpAnnotation\Utility\DocCommand;
use EasySwoole\Phpunit\PhpunitCommand;


class CommandRunner
{
    use Singleton;

    public function __construct()
    {
        CommandManager::getInstance()->addCommand(new Install());
        CommandManager::getInstance()->addCommand(new Task());
        CommandManager::getInstance()->addCommand(new Crontab());
        CommandManager::getInstance()->addCommand(new Process());
        CommandManager::getInstance()->addCommand(new Server());
        CommandManager::getInstance()->addCommand(new PhpunitCommand());
        //预防日后注解库DocCommand有变动影响到主库
        if (class_exists(DocCommand::class)) {
            CommandManager::getInstance()->addCommand(new DocCommand());
        }

        if (class_exists(PhpunitCommand::class)) {
            CommandManager::getInstance()->addCommand(new PhpunitCommand());
        }
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

        $msg = CommandManager::getInstance()->run($caller);

        $result = new Result();
        $result->setMsg($msg);
        return $result;
    }
}
