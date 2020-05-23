<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\CommandInterface;

class Task implements CommandInterface
{
    public function commandName(): string
    {
        return 'task';
    }

    public function exec(...$arg)
    {
        // TODO: Implement exec() method.
    }

}