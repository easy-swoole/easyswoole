<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:11
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\File;

class Install implements CommandInterface
{
    public function commandName(): string
    {
        return 'install';
    }

    public function desc(): string
    {
        return 'Easyswoole framework installation';
    }

    public function exec(): ?string
    {
        if (is_file(EASYSWOOLE_ROOT . '/easyswoole')) {
            unlink(EASYSWOOLE_ROOT . '/easyswoole');
        }
        file_put_contents(EASYSWOOLE_ROOT . '/easyswoole', file_get_contents(__DIR__ . '/../../Resource/easyswoole'));
        Utility::releaseResource(__DIR__ . '/../../Resource/Http/Index._php', EASYSWOOLE_ROOT . '/App/HttpController/Index.php',true);
        Utility::releaseResource(__DIR__ . '/../../Resource/Http/Router._php', EASYSWOOLE_ROOT . '/App/HttpController/Router.php',true);
        Utility::releaseResource(__DIR__ . '/../../Resource/Config._php', EASYSWOOLE_ROOT . '/dev.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Config._php', EASYSWOOLE_ROOT . '/produce.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/bootstrap._php', EASYSWOOLE_ROOT . '/bootstrap.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/EasySwooleEvent._php', EASYSWOOLE_ROOT . '/EasySwooleEvent.php');
        $this->updateComposerJson();
        $this->execComposerDumpAutoload();
        $this->checkFunctionsOpen();
        return Color::success("install success,enjoy!!!\ndont forget run composer dump-autoload !!!");
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        return $commandHelp;
    }

    protected function checkFunctionsOpen()
    {
        if (!function_exists('symlink') || !function_exists('readlink')) {
            echo Color::warning('Please at php.ini Open the symlink and readlink functions') . PHP_EOL;
        }
    }

    protected function updateComposerJson()
    {
        $arr = json_decode(file_get_contents(EASYSWOOLE_ROOT . '/composer.json'), true);
        $arr['autoload']['psr-4']['App\\'] = "App/";
        File::createFile(EASYSWOOLE_ROOT . '/composer.json', json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function execComposerDumpAutoload()
    {
        @exec('composer dump-autoload');
    }
}