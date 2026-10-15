<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:11
 */

namespace EasySwoole\EasySwoole\Command;




use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\Result;
use EasySwoole\Command\Manager;
use EasySwoole\Command\Color;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Command\DefaultCommand\Crontab;
use EasySwoole\EasySwoole\Command\DefaultCommand\Install;
use EasySwoole\EasySwoole\Command\DefaultCommand\Process;
use EasySwoole\EasySwoole\Command\DefaultCommand\Server;
use EasySwoole\EasySwoole\Command\DefaultCommand\Task;


class CommandRunner extends Manager
{
    use Singleton;

    protected Caller|null $caller = null;

    public function __construct()
    {
        $this->addCommand(new Install());
        $this->addCommand(new Server());
        $this->addCommand(new Process());
        $this->addCommand(new Crontab());
        $this->addCommand(new Task());
    }


    public function exec(Caller $caller): Result
    {
        Utility::opCacheClear();
        $this->caller = $caller;
        return parent::exec($caller);
    }

    public function result2HelpMsg(Caller $caller, Result $result): string
    {
        $help = parent::result2HelpMsg($caller, $result);
        if ($caller->command === null || $caller->command === '') {
            return Color::green(Utility::easySwooleLog()) . "\n" . $help;
        }
        return $help;
    }

    public function getCaller(): Caller|null
    {
        return $this->caller;
    }
}
