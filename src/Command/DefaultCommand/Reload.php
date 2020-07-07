<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-25
 * Time: 11:12
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\CallerInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;

class Reload extends AbstractCommand
{
    protected $helps = [
        'reload',
        'reload [produce]'
    ];

    public function commandName(): string
    {
        return 'reload';
    }

    public function exec(CallerInterface $caller): ResultInterface
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
        $result = new Result();
        $result->setMsg($msg);
        return $result;
    }
}
