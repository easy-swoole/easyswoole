<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-03-18
 * Time: 23:21
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;

class Phar implements CommandInterface
{

    public function commandName(): string
    {
        // TODO: Implement commandName() method.
        return 'phar';
    }

    public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
        $name = array_shift($args);
        if(empty($name)){
            $name = 'easyswoole.phar';
        }else{
            $name = "{$name}.phar";
        }
        $phar = new \Phar($name);
        $phar->compressFiles(\Phar::GZ);;
        $phar->stopBuffering();
        $phar->buildFromDirectory();
    }

    public function help(array $args): ?string
    {
        // TODO: Implement help() method.
    }
}