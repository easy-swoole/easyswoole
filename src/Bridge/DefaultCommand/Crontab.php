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

    static function info(Package $package,Package $response)
    {
        $info = \EasySwoole\EasySwoole\Crontab\Crontab::getInstance()->infoTable();
        $data = [];
        foreach ($info as $k => $v) {
            $data[$k] = $v;
        }
        $response->setArgs($data);
    }

    static function stop(Package $package,Package $response)
    {
        $contabName = $package->getArgs();
        $info = \EasySwoole\EasySwoole\Crontab\Crontab::getInstance()->infoTable();
        $crontab = $info->get($contabName);
        if (empty($crontab)) {
            $response->setArgs("crontab is not found.");
            return false;
        }
        if ($crontab['isStop'] == 1) {
            $response->setArgs("crontab is already stop.");
            return false;
        }

        $info->set($contabName, ['isStop' => 1]);
        $response->setArgs("crontab:test is stop suceess.");
        return true;
    }

    static function resume(Package $package,Package $response)
    {
        $contabName = $package->getArgs();
        $info = \EasySwoole\EasySwoole\Crontab\Crontab::getInstance()->infoTable();
        $crontab = $info->get($contabName);
        if (empty($crontab)) {
            $response->setArgs("crontab is not found.");
            return false;
        }
        if ($crontab['isStop'] == 0) {
            $response->setArgs("crontab is running.");
            return false;
        }
        $info->set($contabName, ['isStop' => 0]);
        $response->setArgs("crontab:test resume suceess.");
        return true;
    }

}
