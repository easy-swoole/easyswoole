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
        $response->setArgs($data);
    }

    protected function stop(Package $package, Package $response)
    {
        $contabName = $package->getArgs()['taskName'];
        $info = EasySwooleCron::getInstance()->infoTable();
        $crontab = $info->get($contabName);
        if (empty($crontab)) {
            $response->setMsg("crontab:{$contabName} is not found.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        if ($crontab['isStop'] == 1) {
            $response->setMsg("crontab:{$contabName} is already stop.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }

        $info->set($contabName, ['isStop' => 1]);
        $response->setMsg("crontab:{$contabName} is stop suceess.");
        return true;
    }

    protected function resume(Package $package, Package $response)
    {
        $contabName = $package->getArgs()['taskName'];
        $info = EasySwooleCron::getInstance()->infoTable();
        $crontab = $info->get($contabName);
        if (empty($crontab)) {
            $response->setMsg("crontab:{$contabName} is not found.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        if ($crontab['isStop'] == 0) {
            $response->setMsg("crontab:{$contabName} is running.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        $info->set($contabName, ['isStop' => 0]);
        $response->setMsg("crontab:{$contabName} resume suceess.");
        return true;
    }

    protected function run(Package $package, Package $response)
    {
        $contabName = $package->getArgs()['taskName'];
        $result = EasySwooleCron::getInstance()->rightNow($contabName);
        if ($result === false) {
            $response->setMsg("crontab:{$contabName} is not found.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        if ($result <= 0) {
            $response->setMsg("crontab:{$contabName} run error.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        $response->setMsg("crontab:{$contabName} run success");
        return true;
    }

}
