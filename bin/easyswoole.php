#!/usr/bin/env php
<?php

define('EASYSWOOLE_ROOT', realpath(getcwd()));

$file = EASYSWOOLE_ROOT.'/vendor/autoload.php';
if (file_exists($file)) {
    require $file;
}else{
    die('include composer autoload.php fail');
}


class Install
{
    public static function init()
    {
        \EasySwoole\Frame\Core::getInstance();
        //强制更新easyswoole bin管理文件
        if(is_file(EASYSWOOLE_ROOT . '/easyswoole')){
            unlink(EASYSWOOLE_ROOT . '/easyswoole');
        }
        $path = '.'.str_replace(EASYSWOOLE_ROOT,'',__FILE__);
        file_put_contents(EASYSWOOLE_ROOT . '/easyswoole',"<?php require '{$path}';");
        self::releaseResource(__DIR__ . '/../src/Resource/Config.tpl', EASYSWOOLE_ROOT . '/Config.php');
        self::releaseResource(__DIR__ . '/../src/Resource/EasySwooleEvent.tpl', EASYSWOOLE_ROOT . '/EasySwooleEvent.php');
    }

    public static function releaseResource($source, $destination)
    {
        clearstatcache();
        $replace = true;
        if (is_file($destination)) {
            $filename = basename($destination);
            echo "{$filename} has already existed, do you want to replace it? [ Y / N (default) ] : ";
            $answer = strtolower(trim(strtoupper(fgets(STDIN))));
            if (!in_array($answer, [ 'y', 'yes' ])) {
                $replace = false;
            }
        }
        if ($replace) {
            copy($source, $destination);
        }
    }

    public static function showLogo()
    {
        echo <<<LOGO
  ______                          _____                              _
 |  ____|                        / ____|                            | |
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |
                         |___/

LOGO;
    }
}

Install::showLogo();


$com = new \EasySwoole\Utility\CommandLine();
$config = \EasySwoole\Frame\Config::getInstance();

//设置参数回调
$com->setOptionCallback('d',function ()use($config){
    $config->set('MAIN_SERVER.SETTING.daemonize',true);
    var_dump('set d',func_get_args());
});

//设置命令回调
$com->setArgCallback($com::ARG_DEFAULT_CALLBACK,function (){
   var_dump('输出命令提示');
});

$com->setArgCallback('install',function ()use($config){
    Install::init();
});

$com->setArgCallback('start',function ()use($config){

   var_dump('start',$config->get('MAIN_SERVER.SETTING.daemonize'));
});

$com->setArgCallback('stop',function ()use($com){
    //可以 -f
    var_dump('stop',$com->getOptVal('f'));
});

$com->setArgCallback('reload',function (){
    var_dump('reload');
});



$com->parseArgs($argv);

