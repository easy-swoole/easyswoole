<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/29
 * Time: 9:55 PM
 */

namespace EasySwoole\EasySwoole\Console;


use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

interface ModuleInterface
{
    public function moduleName():string;
    public function exec(Caller $caller,Response $response);
    public function help(Caller $caller,Response $response);
}