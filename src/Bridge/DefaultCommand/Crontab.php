<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Crontab\Protocol\Response;
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
        $info = EasySwooleCron::getInstance()->schedulerTable();
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
        $taskName = $package->getArgs()['taskName'];
        if (!$taskName) {
            $response->setMsg("The name of the operation plan task must be filled in");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        $info = EasySwooleCron::getInstance()->schedulerTable();
        $crontab = $info->get($taskName);
        if (empty($crontab)) {
            $response->setMsg("crontab: {$taskName} is not found.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        if ($crontab['isStop'] == 1) {
            $response->setMsg("crontab: {$taskName} is already stop.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }

        $info->set($taskName, ['isStop' => 1]);
        $response->setMsg("crontab: {$taskName} is stop suceess.");
        return true;
    }

    protected function resume(Package $package, Package $response)
    {
        $taskName = $package->getArgs()['taskName'];
        if (!$taskName) {
            $response->setMsg("The name of the operation plan task must be filled in");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        $info = EasySwooleCron::getInstance()->schedulerTable();
        $crontab = $info->get($taskName);
        if (empty($crontab)) {
            $response->setMsg("crontab: {$taskName} is not found.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        if ($crontab['isStop'] == 0) {
            $response->setMsg("crontab: {$taskName} is running.");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        $info->set($taskName, ['isStop' => 0]);
        $response->setMsg("crontab: {$taskName} resume suceess.");
        return true;
    }

    protected function run(Package $package, Package $response)
    {
        $taskName = $package->getArgs()['taskName'];
        if (!$taskName) {
            $response->setMsg("The name of the operation plan task must be filled in");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        $result = EasySwooleCron::getInstance()->rightNow($taskName);
        if (!$result || !$result instanceof Response) {
            $response->setMsg("crontab: server connect fail");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }

        if ($result->getStatus() != Response::STATUS_OK) {
            $response->setMsg($result->getMsg() ?? Response::getReasonPhrase($result->getStatus()));
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }

        $response->setMsg("crontab: {$taskName} run success");
        return true;
    }

    protected function reset(Package $package, Package $response)
    {
        $taskName = $package->getArgs()['taskName'];
        $taskRule = $package->getArgs()['taskRule'];
        if (!$taskName) {
            $response->setMsg("The name of the operation plan task must be filled in");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }

        if (!$taskRule) {
            $response->setMsg("The rule of the operation plan task must be filled in");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }

        EasySwooleCron::getInstance()->resetJobRule($taskName, $taskRule);

        $response->setMsg("crontab: {$taskName} reset success");
        return true;
    }
}
