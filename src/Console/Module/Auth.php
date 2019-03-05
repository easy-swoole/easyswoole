<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-03-05
 * Time: 22:15
 */

namespace EasySwoole\EasySwoole\Console\Module;


use EasySwoole\Console\ModuleInterface;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

class Auth implements ModuleInterface
{

    public function moduleName(): string
    {
        // TODO: Implement moduleName() method.
        return 'auth';
    }

    public function exec(Caller $caller, Response $response)
    {
        // TODO: Implement exec() method.
    }

    public function help(Caller $caller, Response $response)
    {
        // TODO: Implement help() method.
    }
}