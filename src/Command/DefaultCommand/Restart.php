<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:57
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\CallerInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;

class Restart extends AbstractCommand
{
    protected $helps = [
        'restart',
        'restart [d]',
        'restart [produce]',
        'restart [produce] [d]'
    ];

    public function commandName(): string
    {
        return 'restart';
    }

    public function exec(CallerInterface $caller): ResultInterface
    {
        echo (new Stop())->exec($caller)->getMsg() . "\n";
        (new Start())->exec($caller);
        return new Result();
    }
}
