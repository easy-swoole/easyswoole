<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-25
 * Time: 11:12
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;

class Reload extends AbstractCommand
{
    public function commandName(): string
    {
        return 'reload';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        return $commandHelp;
    }

    public function exec(): string
    {
        $pidFile = Config::getInstance()->getConf("MAIN_SERVER.SETTING.pid_file");
        if (file_exists($pidFile)) {
            Utility::opCacheClear();
            $pid = file_get_contents($pidFile);
            if (!\Swoole\Process::kill($pid, 0)) {
                $msg = "pid :{$pid} not exist ";
            } else {
                \Swoole\Process::kill($pid, SIGUSR1);
                $msg = "send server reload command to pid:{$pid} at " . date("Y-m-d H:i:s");
            }
        } else {
            $msg = "pid file does not exist, please check whether to run in the daemon mode!";
        }
        return $msg;
    }
}
