<?php

use EasySwoole\EasySwoole\Command\CommandRunner;

$file = null;
$trys = [
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php'
];
foreach ($trys as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

if (!file_exists($file)) {
    fwrite(STDERR, "include composer autoload.php fail\n");
    exit(1);
}

$realCwd = substr(realpath($file),0,-strlen("/vendor/autoload.php"));

defined('IN_PHAR') or define('IN_PHAR', boolval(\Phar::running(false)));
defined('RUNNING_ROOT') or define('RUNNING_ROOT', $realCwd);
defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', IN_PHAR ? \Phar::running() : $realCwd);

if(file_exists(EASYSWOOLE_ROOT.'/bootstrap.php')){
    require_once EASYSWOOLE_ROOT.'/bootstrap.php';
}


exit(CommandRunner::getInstance()->run(array_slice($argv, 1)));
