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
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Command\DefaultCommand\Crontab;
use EasySwoole\EasySwoole\Command\DefaultCommand\Install;
use EasySwoole\EasySwoole\Command\DefaultCommand\Process;
use EasySwoole\EasySwoole\Command\DefaultCommand\Server;


class CommandRunner extends Manager
{
    use Singleton;

    public function __construct()
    {
        $this->addCommand(new Install());
        $this->addCommand(new Server());
    }


    public function exec(Caller $caller): Result
    {
        Utility::opCacheClear();
        return parent::exec($caller);
    }
}
