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
use EasySwoole\Utility\File;

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
        $this->updateComposerJson();
        $this->execComposerDumpAutoload();
        echo chr(27)."[42mdont forget run composer dump-autoload ".chr(27)."[0m \n";
        return null;
    }

    protected function updateComposerJson(){
        $arr = json_decode(file_get_contents(EASYSWOOLE_ROOT.'/composer.json'),true);
        $arr['autoload']['psr-4']['App\\'] = "App/";
        File::createFile(EASYSWOOLE_ROOT.'/composer.json',json_encode($arr,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }

    protected function execComposerDumpAutoload(){
        @exec('composer dump-autoload');
    }

    public function help(array $args): ?string
    {
        // TODO: Implement help() method.
        $logo = Utility::easySwooleLog();
        return $logo.'install or reinstall EasySwoole';
    }
}