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
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Option;
use EasySwoole\Command\Bean\Result;
use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Utility\ArrayToTextTable;
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

            $displayItem = Utility::generateEnv(Config::getInstance(),Core::getInstance()->runMode());
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

        $action = new Action('reloadTask', 'reload EasySwoole custom Task workers');
        $action->addOption(new Option('mode', 'run mode, such as --mode=dev'));
        $action->addOption(new class('force', 'use SIGKILL with --force=true; default SIGTERM') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                return $value === null || filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null
                    ? true : 'force must be a boolean, such as --force=true or --force=false';
            }
        });
        $action->setCallback(function (Caller $caller, Result $result): void {
            $force = $caller->commandLine->hasOption('force')
                && ($caller->commandLine->getOption('force') === null
                    || filter_var($caller->commandLine->getOption('force'), FILTER_VALIDATE_BOOLEAN));
            $signal = $force ? SIGKILL : SIGTERM;
            $scheduler = new Scheduler();
            $scheduler->add(function () use ($result, $signal): void {
                try {
                    $package = Bridge::bridgeCall('taskWorkerPidList');
                    if ($package->getStatus() !== StatusEnum::SUCCESS) {
                        $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                        $result->msg = $package->getMsg();
                        return;
                    }
                    $pids = $package->getArgs();
                    if (!is_array($pids)) {
                        throw new \UnexpectedValueException('invalid Task worker PID list');
                    }
                    foreach ($pids as $pid) {
                        if (!is_int($pid) || $pid <= 0) {
                            throw new \UnexpectedValueException('invalid Task worker PID');
                        }
                    }
                    $sent = [];
                    $failed = [];
                    foreach (array_unique($pids) as $pid) {
                        if ($this->signalTaskWorker($pid, $signal)) {
                            $sent[] = $pid;
                        } else {
                            $failed[] = $pid;
                        }
                    }
                    $result->result = ['signal' => $signal, 'sent' => $sent, 'failed' => $failed];
                    if ($failed) {
                        $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                        $result->msg = 'failed to signal Task worker PIDs: ' . implode(', ', $failed);
                    } else {
                        $signalName = $signal === SIGKILL ? 'SIGKILL' : 'SIGTERM';
                        $result->msg = $sent
                            ? "sent {$signalName} to Task worker PIDs: " . implode(', ', $sent)
                            : 'no Task workers available to reload';
                    }
                } catch (\Throwable $throwable) {
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    $result->msg = $throwable->getMessage();
                }
            });
            $scheduler->start();
        });
        $this->registerAction($action);

        $action = new Action('status','get EasySwoole server status');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));

        $action->setCallback(function (Caller $caller,Result $result) {
            $run = new Scheduler();
            $run->add(function () use (&$msg) {
                $package = Bridge::bridgeCall( 'serverStatus');
                if($package->getStatus() == StatusEnum::SUCCESS){
                    $args = $package->getArgs();
                    $msg = Color::green(Utility::easySwooleLog()) . "\n";
                    foreach ($args['env'] as $key => $value) {
                        $msg .= Utility::displayItem($key, $value) . "\n";
                    }
                    $runtimes = $args['runtime'];
                    $runtimes['start_time'] = date("Y-m-d H:i:s",$runtimes['start_time']);

                    $temp = [];
                    //构造表头
                    foreach ($runtimes as $key => $value) {
                        $temp[$key] = $value;
                        unset($runtimes[$key]);
                        if(count($temp) == 5){
                           $temp = [$temp];
                           break;
                        }
                    }
                    //拼接 ，一行表头一行值
                    $headers = [];
                    $values = [];
                    foreach ($runtimes as $key => $value) {
                        $values[] = $value;
                        $headers[] = $key;
                        if(count($headers) == 5){
                            $t2 = [];
                            $t3 = [];
                            foreach ($temp[0] as $header => $t) {
                                $t2[$header] = array_pop($headers);
                                $t3[$header] = array_pop($values);
                            }
                            $temp[] = $t2;
                            $temp[] = $t3;
                            $headers = [];
                            $values = [];
                        }
                    }
                    if(!empty($headers)){
                        $t2 = [];
                        $t3 = [];
                        foreach ($temp[0] as $header => $t) {
                            $t2[$header] = array_pop($headers);
                            $t3[$header] = array_pop($values);
                        }
                        $temp[] = $t2;
                        $temp[] = $t3;
                    }
                    $msg .= new ArrayToTextTable($temp);
                }else{
                    $msg = Color::error($package->getMsg());
                }
            });
            $run->start();
            $result->msg = $msg;
        });

        $this->registerAction($action);
    }

    protected function signalTaskWorker(int $pid, int $signal): bool
    {
        return \Swoole\Process::kill($pid, $signal);
    }

}
