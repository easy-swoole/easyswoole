<?php

define('EASYSWOOLE_ROOT', realpath(getcwd()));

use EasySwoole\Frame\Config as Conf;
use EasySwoole\Frame\Core;
use EasySwoole\Frame\SysConst;

foreach ([ __DIR__ . '/../../../autoload.php', __DIR__ . '/../vendor/autoload.php' ] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

/**
 * 解析命令行输入
 * @return array
 */
function commandParser()
{
    global $argv;
    $command = '';
    $options = array();
    if (isset($argv[1])) {
        $command = $argv[1];
    }
    foreach ($argv as $item) {
        if (substr($item, 0, 2) === '--') {
            $temp = trim($item, "--");
            $temp = explode("-", $temp);
            $key = array_shift($temp);
            $options[$key] = array_shift($temp) ?: '';
        }
    }
    return array( $command, $options );
}

/**
 * 缓存清除
 */
function opCacheClear()
{
    if (function_exists('apc_clear_cache')) {
        apc_clear_cache();
    }
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
}

/**
 * 环境检查
 */
function envCheck()
{
    if (version_compare(phpversion(), '7.1', '<')) {
        die("PHP version\e[31m must >= 7.1\e[0m\n");
    }
    if (version_compare(phpversion('swoole'), '1.9.5', '<')) {
        die("Swoole extension version\e[31m must >= 1.9.5\e[0m\n");
    }
    if (!class_exists(\EasySwoole\Frame\Core::class)) {
        die("Autoload fail!\nPlease try to run\e[31m composer install\e[0m in " . EASYSWOOLE_ROOT . "\n");
    }
}

/**
 * 安装检查
 */
function installCheck()
{
    $lockFile = EASYSWOOLE_ROOT . '/easyswoole.install';
    if (!is_file($lockFile)) {
        die("easySwoole framework has not been installed, Please run\e[031m easyswoole install\e[0m\n");
    }
}

/**
 * 释放资源
 * @param string $source      源位置
 * @param string $destination 目标位置
 */
function releaseResource($source, $destination)
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

/**
 * 配置初始化
 */
function initConf()
{
    releaseResource(__DIR__ . '/../src/Resource/Config.tpl', EASYSWOOLE_ROOT . '/Config.php');
    releaseResource(__DIR__ . '/../src/Resource/EasySwooleEvent.tpl', EASYSWOOLE_ROOT . '/EasySwooleEvent.php');
}

/**
 * 输出标签
 * @param string $name  标签名
 * @param string $value 标签值
 */
function showTag($name, $value)
{
    echo "\e[32m" . str_pad($name, 20, ' ', STR_PAD_RIGHT) . "\e[34m" . $value . "\e[0m\n";
}

/**
 * 相对路径转为绝对路径
 * @param $a
 * @param $b
 * @return string
 */
function getRelativelyPath($a, $b)
{
    $arr1 = explode('/', $a);
    $arr2 = explode('/', $b);
    $intersection = array_intersect_assoc($arr1, $arr2);
    $depth = 0;
    for ($i = 0, $len = count($intersection); $i < $len; $i++) {
        $depth = $i;
        if (!isset($intersection[$i])) {
            break;
        }
    }

    if ($i == count($intersection)) {
        $depth++;
    }

    if (count($arr2) - $depth - 1 > 0) {
        $prefix = array_fill(0, count($arr2) - $depth - 1, '..');
    } else {
        $prefix = array( '.' );
    }

    $tmp = array_merge($prefix, array_slice($arr1, $depth));
    $relativePath = implode('/', $tmp);
    return $relativePath;
}

/**
 * 命令分发
 */
function commandHandler()
{
    list($command, $options) = commandParser();
    switch ($command) {
        case 'start':
            installCheck();
            serverStart($options);
            break;
        case 'stop':
            installCheck();
            serverStop($options);
            break;
        case 'reload':
            installCheck();
            serverReload($options);
            break;
        case 'install':
            serverInstall($options);
            break;
        case 'restart':
            serverRestart($options);
            break;
        case 'help':
        default:
            showHelp($options);
    }
}

/**
 * 输出LOGO
 */
function showLogo()
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

/**
 * 显示帮助文本
 * @param $options
 */
function showHelp($options)
{
    $opName = '';
    $args = array_keys($options);
    if ($args) $opName = $args[0];

    switch ($opName) {
        case 'start':
            echo <<<HELP_START
\e[33m操作:\e[0m
\e[31m  easyswoole start\e[0m
\e[33m简介:\e[0m
\e[36m  执行本命令可以启动框架 可选的操作参数如下\e[0m
\e[33m参数:\e[0m
\e[32m  --d \e[0m                   以守护模式启动框架
\e[32m  --ip\e[34m-address \e[0m          指定服务监听地址
\e[32m  --p\e[34m-portNumber \e[0m        指定服务监听端口
\e[32m  --pid\e[34m-fileName \e[0m        指定服务PID存储文件
\e[32m  --workerNum\e[34m-num \e[0m       设置worker进程数
\e[32m  --taskWorkerNum\e[34m-num \e[0m   设置Task进程数
\e[32m  --user\e[34m-userName \e[0m       指定以某个用户身份执行
\e[32m  --group\e[34m-groupName \e[0m     指定以某个用户组身份执行
\e[32m  --cpuAffinity \e[0m         开启CPU亲和\n
HELP_START;
            break;
        case 'stop':
            echo <<<HELP_STOP
\e[33m操作:\e[0m
\e[31m  easyswoole stop\e[0m
\e[33m简介:\e[0m
\e[36m  执行本命令可以停止框架 可选的操作参数如下\e[0m
\e[33m参数:\e[0m
\e[32m  --f \e[0m             强制停止服务
\e[32m  --pid\e[34m-pidFile \e[0m   指定服务PID存储文件\n
HELP_STOP;
            break;
        case 'reload':
            echo <<<HELP_STOP
\e[33m操作:\e[0m
\e[31m  easyswoole reload\e[0m
\e[33m简介:\e[0m
\e[36m  执行本命令可以重启所有Worker 可选的操作参数如下\e[0m
\e[33m参数:\e[0m
\e[32m  --all \e[0m           重启所有进程
\e[32m  --pid\e[34m-pidFile \e[0m   指定服务PID存储文件\n
HELP_STOP;
            break;
        case 'install':
            echo <<<HELP_INSTALL
\e[33m操作:\e[0m
\e[31m  easyswoole install\e[0m
\e[33m简介:\e[0m
\e[36m  安装并初始化easySwoole相关目录\e[0m
\e[33m参数:\e[0m
\e[32m  --f \e[0m             强制重新安装
HELP_INSTALL;
            break;
        case 'restart':
            echo <<<HELP_INSTALL
\e[33m操作:\e[0m
\e[31m  easyswoole restart\e[0m
\e[33m简介:\e[0m
\e[36m  停止并重新启动服务\e[0m
\e[33m参数:\e[0m
\e[32m  本操作没有相关的参数\e[0m\n
HELP_INSTALL;
            break;

        default:
            showLogo();
            echo <<<DEFAULTHELP
\n欢迎使用为API而生的\e[32m easySwoole\e[0m 框架 当前版本: \e[34m3.x\e[0m

\e[33m使用:\e[0m
  easyswoole [操作] [选项]

\e[33m操作:\e[0m
\e[32m  install \e[0m      初始化easySwoole
\e[32m  start \e[0m        启动服务
\e[32m  stop \e[0m         停止服务
\e[32m  reload \e[0m       重载服务
\e[32m  restart \e[0m      重启服务

\e[32m  help \e[0m         查看命令的帮助信息\n
\e[31m有关某个操作的详细信息 请使用\e[0m help \e[31m命令查看 \e[0m
\e[31m如查看\e[0m start \e[31m操作的详细信息 请输入\e[0m easyswoole help --start\n\n
DEFAULTHELP;
    }
}

/**
 * 框架安装
 * @param $options
 */
function serverInstall($options)
{
    $lockFile = EASYSWOOLE_ROOT . '/easyswoole.install';
    if (!is_file($lockFile) || isset($options['f'])) {
        initConf();
        file_put_contents($lockFile, 'installed at ' . date('Y-m-d H:i:s'));
        $realPath = getRelativelyPath(__DIR__ . '/easyswoole', EASYSWOOLE_ROOT);
        file_put_contents(EASYSWOOLE_ROOT . '/easyswoole', "<?php\nrequire '$realPath';");
        echo "easySwoole server install complete!\n";
    } else {
        die("easySwoole framework has been installed\nPlease remove \e[31m{$lockFile}\e[0m and try again\n");
    }
}

/**
 * 服务启动
 * @param $options
 */
function serverStart($options)
{
    showLogo();
    $conf = Conf::getInstance();
    $inst = Core::getInstance()->initialize();
    $version = SysConst::VERSION;
    echo "\n\e[31mEasySwoole\e[0m framework \e[34mVersion {$version}\e[0m\n\n";

    // set config
    if (isset($options['ip'])) $conf->setConf("MAIN_SERVER.HOST", $options['ip']);
    if (!empty($options['p'])) $conf->setConf("MAIN_SERVER.PORT", $options['p']);
    if (!empty($options['pid'])) $conf->setConf("MAIN_SERVER.SETTING.pid_file", $options['pid']);
    if (isset($options['workerNum'])) $conf->setConf("MAIN_SERVER.SETTING.worker_num", $options['workerNum']);
    if (isset($options['taskWorkerNum'])) $conf->setConf("MAIN_SERVER.SETTING.task_worker_num", $options['taskWorkerNum']);
    if (isset($options['user'])) $conf->setConf("MAIN_SERVER.SETTING.user", $options['user']);
    if (isset($options['d'])) $conf->setConf("MAIN_SERVER.SETTING.daemonize", true);
    if (isset($options['cpuAffinity'])) $conf->setConf("MAIN_SERVER.SETTING.open_cpu_affinity", true);

    // show status
    showTag('server name', $conf->getConf('SERVER_NAME'));
    showTag('listen address', $conf->getConf('MAIN_SERVER.HOST'));
    showTag('listen port', $conf->getConf('MAIN_SERVER.PORT'));
    showTag('worker num', $conf->getConf('MAIN_SERVER.SETTING.worker_num'));
    showTag('task worker num', $conf->getConf('MAIN_SERVER.SETTING.task_worker_num'));
    $user = $conf->getConf('MAIN_SERVER.SETTING.user');
    showTag('run at user', $user ? $user : get_current_user());
    showTag('daemonize', $conf->getConf('MAIN_SERVER.SETTING.daemonize') === true ? 'true' : 'false');
    showTag('swoole version', phpversion('swoole'));
    showTag('php version', phpversion());

    $inst->createServer()->start();
}

/**
 * 停止服务
 * @param $options
 * @return bool
 */
function serverStop($options)
{
    if (!empty($options['pid'])) $pidFile = $options['pid'];
    else {
        Core::getInstance()->initialize();
        $pidFile = Conf::getInstance()->getConf('MAIN_SERVER.SETTING.pid_file');
    }
    if (file_exists($pidFile)) {
        $pid = file_get_contents($pidFile);
        if (!swoole_process::kill($pid, 0)) {
            echo "PID :{$pid} not exist \n";
            return false;
        }
        if (in_array('-f', $options)) {
            swoole_process::kill($pid, SIGKILL);
        } else {
            swoole_process::kill($pid);
        }
        //等待5秒
        $time = time();
        $flag = false;
        while (true) {
            usleep(1000);
            if (!swoole_process::kill($pid, 0)) {
                echo "server stop at " . date("y-m-d h:i:s") . "\n";
                if (is_file($pidFile)) {
                    unlink($pidFile);
                }
                $flag = true;
                break;
            } else {
                if (time() - $time > 5) {
                    echo "stop server fail.try -f again \n";
                    break;
                }
            }
        }
        return $flag;
    } else {
        echo "PID file does not exist, please check whether to run in the daemon mode!\n";
        return false;
    }

}

/**
 * 服务重启
 * @param $options
 */
function serverReload($options)
{
    Core::getInstance()->initialize();
    $Conf = Conf::getInstance();
    $pidFile = $Conf->getConf("MAIN_SERVER.SETTING.pid_file");
    if (!empty($options['pid'])) {
        $pidFile = $options['pid'];
    }
    if (file_exists($pidFile)) {
        if (isset($options['onlyTask'])) {
            $sig = SIGUSR2;
        } else {
            $sig = SIGUSR1;
        }

        opCacheClear();
        $pid = file_get_contents($pidFile);
        if (!swoole_process::kill($pid, 0)) {
            echo "pid :{$pid} not exist \n";
            return;
        }
        swoole_process::kill($pid, $sig);
        echo "send server reload command at " . date("y-m-d h:i:s") . "\n";

    } else {
        echo "PID file does not exist, please check whether to run in the daemon mode!\n";
    }
}

function serverRestart($options)
{
    if (serverStop($options)) {
        $options['d'] = '';
        serverStart($options);
    }
}

envCheck();
commandHandler();