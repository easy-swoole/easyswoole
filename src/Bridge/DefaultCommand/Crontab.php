<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2020/4/7 0007
 * Time: 15:51
 */

namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\EasySwoole\Bridge\BridgeCommand;
use EasySwoole\EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Task\TaskManager;

class Crontab extends Base
{
    static function initCommand(BridgeCommand $command)
    {
        $command->set(BridgeCommand::CRON_INFO, [Crontab::class, 'info']);
        $command->set(BridgeCommand::CRON_STOP, [Crontab::class, 'stop']);
        $command->set(BridgeCommand::CRON_RESUME, [Crontab::class, 'resume']);
    }

    static function info()
    {
        $info = \EasySwoole\EasySwoole\Crontab\Crontab::getInstance()->infoTable();
        $data = [];
        foreach ($info as $k => $v) {
            $data[$k] = $v;
        }
        return $data;
    }

    static function stop(Package $package)
    {
        $contabName = $package->getArgs();
        $info = \EasySwoole\EasySwoole\Crontab\Crontab::getInstance()->infoTable();
        $crontab = $info->get($contabName);
        if (empty($crontab)) {
            return "crontab is not found.";
        }
        if ($crontab['isStop'] == 1) {
            return "crontab is already stop.";
        }

        $info->set($contabName, ['isStop' => 1]);
        return "crontab:test is stop suceess.";
    }

    static function resume(Package $package)
    {
        $contabName = $package->getArgs();
        $info = \EasySwoole\EasySwoole\Crontab\Crontab::getInstance()->infoTable();
        $crontab = $info->get($contabName);
        if (empty($crontab)) {
            return "crontab is not found.";
        }
        if ($crontab['isStop'] == 0) {
            return "crontab is running.";
        }
        $info->set($contabName, ['isStop' => 0]);
        return "crontab:test resume suceess.";
    }

}
