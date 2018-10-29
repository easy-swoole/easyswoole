<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/29
 * Time: 10:08 PM
 */

namespace EasySwoole\EasySwoole\Console\DefaultCommand;


use EasySwoole\EasySwoole\Console\CommandInterface;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

class Auth implements CommandInterface
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