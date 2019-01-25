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
        Utility::releaseResource(__DIR__ . '/../../Resource/EasySwooleEvent.tpl', EASYSWOOLE_ROOT . '/EasySwooleEvent.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Config.tpl', EASYSWOOLE_ROOT . '/dev.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Config.tpl', EASYSWOOLE_ROOT . '/produce.php');
        echo "install success,enjoy! \n";
        return null;
    }

    public function help(array $args): ?string
    {
        // TODO: Implement help() method.
        $logo = Utility::easySwooleLog();
        return $logo.'install or reinstall EasySwoole';
    }
}