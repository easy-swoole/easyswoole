<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:11
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\AbstractCommand;
use EasySwoole\Command\Bean\Action;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\Result;
use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\File;

class Install extends AbstractCommand
{
    function name(): string
    {
        return 'install';
    }

    function description(): string
    {
        return 'EasySwoole framework installation';
    }

    protected function init(): void
    {
        $action = new Action('default');

        $action->setCallback(function (Caller $caller,Result $result) {
            if (is_file(EASYSWOOLE_ROOT . '/easyswoole.php')) {
                unlink(EASYSWOOLE_ROOT . '/easyswoole.php');
            }
            file_put_contents(EASYSWOOLE_ROOT . '/easyswoole.php', file_get_contents(__DIR__ . '/../../Resource/easyswoole'));
            Utility::releaseResource(__DIR__ . '/../../Resource/Http/Index._php', EASYSWOOLE_ROOT . '/App/HttpController/Index.php',true);
            Utility::releaseResource(__DIR__ . '/../../Resource/Http/Router._php', EASYSWOOLE_ROOT . '/App/HttpController/Router.php',true);
            Utility::releaseResource(__DIR__ . '/../../Resource/Config._php', EASYSWOOLE_ROOT . '/dev.php');
            Utility::releaseResource(__DIR__ . '/../../Resource/Config._php', EASYSWOOLE_ROOT . '/produce.php');
            Utility::releaseResource(__DIR__ . '/../../Resource/bootstrap._php', EASYSWOOLE_ROOT . '/bootstrap.php');
            Utility::releaseResource(__DIR__ . '/../../Resource/EasySwooleEvent._php', EASYSWOOLE_ROOT . '/EasySwooleEvent.php');

            //更新composer
            $arr = json_decode(file_get_contents(EASYSWOOLE_ROOT . '/composer.json'), true);
            $arr['autoload']['psr-4']['App\\'] = "App/";
            File::createFile(EASYSWOOLE_ROOT . '/composer.json', json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            //实现compser dumpautoload
            if(function_exists('exec')){
                @exec('composer dump-autoload');
            }else{
                $result->msg = Color::warning('exec() is forbid,you may run composer dump-autoload by manual') . PHP_EOL;
            }

            $result->msg = PHP_EOL.Color::success('install EasySwoole Success');

        });

        $this->registerDefaultAction($action);
    }
}