<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\AbstractInterface\AbstractCommand;
use EasySwoole\Command\Bean\Action;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\Option;
use EasySwoole\Command\Bean\Result;
use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use Swoole\Coroutine\Scheduler;

class Server extends AbstractCommand
{

    function name(): string
    {
        return 'server';
    }

    function description(): string
    {
        return 'EasySwoole server manager';
    }

    public function beforeExecute(Caller $caller, Result $result): bool
    {
        $mode = $caller->commandLine->getOption('mode');
        Core::getInstance()->initialize($mode);
        return true;
    }

    protected function init():void
    {
        $action = new Action('start','start EasySwoole server');
        $action->addOption(new Option('d','start EasySwoole with daemonize mode'));
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->setCallback(function (Caller $caller) {
            defined('EASYSWOOLE_RUNNING') or define('EASYSWOOLE_RUNNING', true);
            $conf = Config::getInstance();
            $daemonize = $caller->commandLine->hasOption('d');
            if ($daemonize) {
                $conf->setConf("MAIN_SERVER.SETTING.daemonize", $daemonize);
            }
            if (empty($conf->getConf('MAIN_SERVER.SETTING.user'))) {
                $conf->setConf('MAIN_SERVER.SETTING.user',get_current_user());
            }

            $displayItem = Utility::createServerDisplayItem(Config::getInstance());
            $msg = Color::green(Utility::easySwooleLog()) . "\n";
            foreach ($displayItem as $key => $value) {
                $msg .= Utility::displayItem($key, $value) . "\n";
            }
            echo $msg;
            Core::getInstance()->createServer()->start();
        });

        $this->registerAction($action);

        $action = new Action('stop','stop EasySwoole server');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->addOption(new Option('force','stop EasySwoole server force with SIGKILL'));
        $action->setCallback(function (Caller $caller,Result $result) {
            $pidFile = Config::getInstance()->getConf("MAIN_SERVER.SETTING.pid_file");
            $msg = '';
            if (file_exists($pidFile)) {
                $pid = intval(file_get_contents($pidFile));
                if (!\Swoole\Process::kill($pid, 0)) {
                    $msg = Color::danger("pid :{$pid} not exist ");
                    unlink($pidFile);
                } else {
                    $force = $caller->commandLine->hasOption('force');
                    if ($force) {
                        \Swoole\Process::kill($pid, SIGKILL);
                    } else {
                        \Swoole\Process::kill($pid);
                    }
                    //等待5秒
                    $time = time();
                    while (true) {
                        usleep(1000);
                        if (!\Swoole\Process::kill($pid, 0)) {
                            if (is_file($pidFile)) {
                                unlink($pidFile);
                            }
                            $msg = "server stop for pid {$pid} at " . date("Y-m-d H:i:s");
                            $msg = Color::success($msg);
                            break;
                        } else {
                            if (time() - $time > 15) {
                                $msg = Color::danger("stop server fail for pid:{$pid} , try [php easyswoole.php server stop -force] again");
                                break;
                            }
                        }
                    }
                }
            } else {
                $msg = Color::danger("pid file does not exist, please check whether to run in the daemon mode!");
            }
            $result->msg = $msg;
        });

        $this->registerAction($action);

        $action = new Action('reload','reload EasySwoole server');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->setCallback(function (Caller $caller,Result $result) {
            $pidFile = Config::getInstance()->getConf("MAIN_SERVER.SETTING.pid_file");
            if (file_exists($pidFile)) {
                $pid = file_get_contents($pidFile);
                if (!\Swoole\Process::kill($pid, 0)) {
                    $msg = Color::danger("pid :{$pid} not exist ");
                } else {
                    \Swoole\Process::kill($pid, SIGUSR1);
                    $msg = "send server reload command to pid:{$pid} at " . date("Y-m-d H:i:s");
                    $msg = Color::success($msg);
                }
            } else {
                $msg = Color::danger("pid file does not exist, please check whether to run in the daemon mode!");
            }
            $result->msg = $msg;
        });
        $this->registerAction($action);

        $action = new Action('status','get EasySwoole server status');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));

        $action->setCallback(function (Caller $caller,Result $result) {
            $run = new Scheduler();
            $run->add(function () use (&$msg) {
                $package = Bridge::bridgeCall( 'serverStatus');
                if($package->getStatus() == StatusEnum::SUCCESS){
                    $displayItem = $package->getArgs();
                    $msg = Color::green(Utility::easySwooleLog()) . "\n";
                    foreach ($displayItem as $key => $value) {
                        $msg .= Utility::displayItem($key, $value) . "\n";
                    }
                }else{
                    $msg = Color::error($package->getMsg());
                }
            });
            $run->start();
            $result->msg = $msg;
        });

        $this->registerAction($action);
    }
}
