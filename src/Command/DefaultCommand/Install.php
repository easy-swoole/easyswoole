<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:11
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\File;

class Install implements CommandInterface
{
    public function commandName(): string
    {
        return 'install';
    }

    public function exec($args): ResultInterface
    {
        $msg = Utility::easySwooleLog();
        if(is_file(EASYSWOOLE_ROOT . '/easyswoole')){
            unlink(EASYSWOOLE_ROOT . '/easyswoole');
        }
        file_put_contents(EASYSWOOLE_ROOT . '/easyswoole',file_get_contents(__DIR__.'/../../../bin/easyswoole'));
        Utility::releaseResource(__DIR__ . '/../../Resource/EasySwooleEvent._php', EASYSWOOLE_ROOT . '/EasySwooleEvent.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Http/Index._php', EASYSWOOLE_ROOT . '/App/HttpController/Index.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Http/Router._php', EASYSWOOLE_ROOT . '/App/HttpController/Router.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Config._php', EASYSWOOLE_ROOT . '/dev.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/Config._php', EASYSWOOLE_ROOT . '/produce.php');
        Utility::releaseResource(__DIR__ . '/../../Resource/bootstrap._php', EASYSWOOLE_ROOT . '/produce.php');
        $this->updateComposerJson();
        $this->execComposerDumpAutoload();
        $msg .= "install success,enjoy!!!\ndont forget run composer dump-autoload !!!";
        $result = new Result();
        $result->setMsg($msg);
        return $result;
    }

    public function help($args): ResultInterface
    {
        $result = new Result();
        $result->setMsg(Utility::easySwooleLog()."\nrun [php easyswoole install] to install easyswoole !!!");
        return $result;
    }

    protected function updateComposerJson(){
        $arr = json_decode(file_get_contents(EASYSWOOLE_ROOT.'/composer.json'),true);
        $arr['autoload']['psr-4']['App\\'] = "App/";
        File::createFile(EASYSWOOLE_ROOT.'/composer.json',json_encode($arr,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }

    protected function execComposerDumpAutoload(){
        @exec('composer dump-autoload');
    }
}