<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;

class Crontab implements CommandInterface
{
    public function commandName(): string
    {
        return 'crontab';
    }

    public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
    }

    public function help(array $args): ?string
    {
        return null;
    }

}