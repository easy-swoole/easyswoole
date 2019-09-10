<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:11
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;

class Install implements CommandInterface
{
    public function commandName(): string
    {
        // TODO: Implement commandName() method.
        return 'install';
    }

    public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
        echo Utility::easySwooleLog();
        //force to update easyswoole file
        if(is_file(EASYSWOOLE_ROOT . '/easyswoole')){
            unlink(EASYSWOOLE_ROOT . '/easyswoole');
        }
        file_put_contents(EASYSWOOLE_ROOT . '/easyswoole',file_get_contents(__DIR__.'/../../../bin/easyswoole'));
        Utility::releaseResource(__DIR__ . '/../../Resource/EasySwooleEvent._php', EASYSWOOLE_ROOT . '/EasySwooleEvent.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Http/Index._php', EASYSWOOLE_ROOT . '/App/HttpController/Index.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Config._php', EASYSWOOLE_ROOT . '/dev.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Config._php', EASYSWOOLE_ROOT . '/produce.php');
        echo chr(27)."[42minstall success,enjoy! ".chr(27)."[0m \n";
        echo chr(27)."[42mdont forget to add the psr-4 namespace map \"App\\\\\":\"App/\" into composer.json and run composer dump-autoload ".chr(27)."[0m \n";
        return null;
    }

    public function help(array $args): ?string
    {
        // TODO: Implement help() method.
        $logo = Utility::easySwooleLog();
        return $logo.'install or reinstall EasySwoole';
    }
}