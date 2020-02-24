<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;

class Process implements CommandInterface
{

    public function commandName(): string
    {
        return 'process';
    }

    public function exec(array $args): ?string
    {
        /*
         * php easyswoole process kill PID
         * php easyswoole process kill PID -f
         * php easyswoole process kill GroupName -f
         * php easyswoole process killAll
         * php easyswoole process killAll -f
         */
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo;
    }
}