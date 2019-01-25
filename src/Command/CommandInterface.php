<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:04
 */

namespace EasySwoole\EasySwoole\Command;


interface CommandInterface
{
    public function commandName():string;
    public function exec(array $args):?string ;
    public function help(array $args):?string ;
}