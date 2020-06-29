<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Bridge\AbstractCommand;
use EasySwoole\EasySwoole\Crontab\Crontab as EasySwooleCron;

class Crontab extends AbstractCommand
{
    public function commandName(): string
    {
        return 'crontab';
    }

    protected function show(Package $package, Package $response)
    {
        $info = EasySwooleCron::getInstance()->infoTable();
        $data = [];
        foreach ($info as $k => $v) {
            $data[$k] = $v;
        }
        if (empty($data)) {
            $response->setMsg("crontab info is abnormal.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        $response->setArgs($data);
    }

    protected function stop(Package $package, Package $response)
    {
        $crontabName = $package->getArgs()['taskName'];
        $info = EasySwooleCron::getInstance()->infoTable();
        $crontab = $info->get($crontabName);
        if (empty($crontab)) {
            $response->setMsg("crontab:{$crontabName} is not found.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        if ($crontab['isStop'] == 1) {
            $response->setMsg("crontab:{$crontabName} is already stop.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }

        $info->set($crontabName, ['isStop' => 1]);
        $response->setMsg("crontab:{$crontabName} is stop suceess.");
        return true;
    }

    protected function resume(Package $package, Package $response)
    {
        $crontabName = $package->getArgs()['taskName'];
        $info = EasySwooleCron::getInstance()->infoTable();
        $crontab = $info->get($crontabName);
        if (empty($crontab)) {
            $response->setMsg("crontab:{$crontabName} is not found.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        if ($crontab['isStop'] == 0) {
            $response->setMsg("crontab:{$crontabName} is running.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        $info->set($crontabName, ['isStop' => 0]);
        $response->setMsg("crontab:{$crontabName} resume suceess.");
        return true;
    }

    protected function run(Package $package, Package $response)
    {
        $crontabName = $package->getArgs()['taskName'];
        $result = EasySwooleCron::getInstance()->rightNow($crontabName);
        if ($result === false) {
            $response->setMsg("crontab:{$crontabName} is not found.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        if ($result <= 0) {
            $response->setMsg("crontab:{$crontabName} run error.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        $response->setMsg("crontab:{$crontabName} run success");
        return true;
    }

}
