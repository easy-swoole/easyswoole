<?php

use EasySwoole\EasySwoole\Command\CommandRunner;
use EasySwoole\Command\Caller;

$file = __DIR__ . '/vendor/autoload.php';
if (file_exists($file)) {
    require $file;
} else {
    die("include composer autoload.php fail\n");
}

$realCwd = substr(realpath($file),0,-strlen("/vendor/autoload.php"));

defined('IN_PHAR') or define('IN_PHAR', boolval(\Phar::running(false)));
defined('RUNNING_ROOT') or define('RUNNING_ROOT', $realCwd);
defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', IN_PHAR ? \Phar::running() : $realCwd);

if(file_exists(EASYSWOOLE_ROOT.'/bootstrap.php')){
    require_once EASYSWOOLE_ROOT.'/bootstrap.php';
}

$caller = new Caller();
$caller->setScript(current($argv));
$caller->setCommand(next($argv));
$caller->setParams($argv);
reset($argv);

$ret = CommandRunner::getInstance()->run($caller);
if($ret && !empty($ret->getMsg())){
    echo $ret->getMsg()."\n";
}
