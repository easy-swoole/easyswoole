<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-03-07
 * Time: 13:57
 */

namespace EasySwoole\EasySwoole\Console\Module;


use EasySwoole\Console\ModuleInterface;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

class Log implements ModuleInterface
{

    public function moduleName(): string
    {
        // TODO: Implement moduleName() method.
        return 'log';
    }

    public function exec(Caller $caller, Response $response)
    {
        // TODO: Implement exec() method.
    }

    public function help(Caller $caller, Response $response)
    {
        // TODO: Implement help() method.
    }

    public static function push(string $str, $logCategory = null)
    {

    }
}