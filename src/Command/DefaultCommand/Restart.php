<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:57
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;

class Restart implements CommandInterface
{
    public function commandName(): string
    {
        return 'restart';
    }

    public function exec($args): ResultInterface
    {
        echo (new Stop())->exec($args)->getMsg()."\n";
        (new Start())->exec($args);
        return new Result();
    }

    public function help($args): ResultInterface
    {
        $result = new Result();
        $msg = Utility::easySwooleLog().<<<HELP_START
php easyswoole restart  
php easyswoole restart [d]
php easyswoole restart [produce]
php easyswoole restart [produce] [d]
HELP_START;
        $result->setMsg($msg);
        return  $result;
    }
}
