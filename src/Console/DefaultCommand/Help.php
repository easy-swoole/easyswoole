<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/29
 * Time: 9:59 PM
 */

namespace EasySwoole\EasySwoole\Console\DefaultCommand;


use EasySwoole\EasySwoole\Console\CommandInterface;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

class Help implements CommandInterface
{

    public function exec(Caller $caller, Response $response)
    {
        // TODO: Implement exec() method.
    }

    public function help(Caller $caller, Response $response)
    {
        // TODO: Implement help() method.
    }
}