<?php

namespace EasySwoole\EasySwoole\Bridge;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Component\Process\Manager;
use EasySwoole\Crontab\Protocol\Response;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Crontab\Crontab as EasySwooleCron;
use EasySwoole\EasySwoole\ServerManager;

class BaseService extends AbstractCommand
{

    public function commandName(): string
    {
        return 'BaseService';
    }

    function serverStatus(Package $request,Package $responsePackage):void
    {
        $data = ServerManager::getInstance()->getSwooleServer()->stats();
        $data = Utility::createServerDisplayItem(Config::getInstance()) + $data;
        $responsePackage->setArgs($data);
    }

    function processInfo(Package $request,Package $responsePackage):void
    {
        $array = Manager::getInstance()->info();
        foreach ($array as &$value){
            unset($value['hash']);
        }
        $responsePackage->setArgs($array);
    }

    function crontabInfo(Package $request,Package $responsePackage):void
    {
        $info = EasySwooleCron::getInstance()->schedulerTable();
        $data = [];
        foreach ($info as $k => $v) {
            $data[$k] = $v;
        }
        if (empty($data)) {
            $responsePackage->setMsg("crontab info is abnormal or empty crontab register");
            $responsePackage->setStatus(StatusEnum::COMMAND_EXEC_ERROR);
        }else{
            $responsePackage->setArgs($data);
        }
    }

    function stopCrontabRule(Package $request,Package $responsePackage):void
    {
        $taskName = $request->getArgs()['taskName'];
        $info = EasySwooleCron::getInstance()->schedulerTable();
        $crontab = $info->get($taskName);
        if (empty($crontab)) {
            $responsePackage->setMsg("crontab job [{$taskName}] is not found");
            $responsePackage->setStatus(StatusEnum::COMMAND_EXEC_ERROR);
            return;
        }
        $info->set($taskName, ['isStop' => 1]);
        $responsePackage->setMsg("crontab job [{$taskName}] is stop success");
    }

    function resumeCrontabRule(Package $request,Package $responsePackage):void
    {
        $taskName = $request->getArgs()['taskName'];
        $info = EasySwooleCron::getInstance()->schedulerTable();
        $crontab = $info->get($taskName);
        if (empty($crontab)) {
            $responsePackage->setMsg("crontab job [{$taskName}] is not found");
            $responsePackage->setStatus(StatusEnum::COMMAND_EXEC_ERROR);
            return;
        }
        $info->set($taskName, ['isStop' => 0]);
        $responsePackage->setMsg("crontab job [{$taskName}] is resume success");
    }

    function runCrontabJobNow(Package $request,Package $responsePackage):void
    {
        $taskName = $request->getArgs()['taskName'];
        $info = EasySwooleCron::getInstance()->schedulerTable();
        $crontab = $info->get($taskName);
        if (empty($crontab)) {
            $responsePackage->setMsg("crontab job [{$taskName}] is not found");
            $responsePackage->setStatus(StatusEnum::COMMAND_EXEC_ERROR);
            return;
        }
        $result = EasySwooleCron::getInstance()->rightNow($taskName);
        if (!$result instanceof Response) {
            $responsePackage->setMsg("crontab server connect fail");
            $responsePackage->setStatus(StatusEnum::COMMAND_EXEC_ERROR);
            return;
        }

        if ($result->getStatus() != Response::STATUS_OK) {
            $responsePackage->setMsg($result->getMsg() ?? Response::getReasonPhrase($result->getStatus()));
            $responsePackage->setStatus(StatusEnum::COMMAND_EXEC_ERROR);
            return;
        }
        $responsePackage->setMsg("crontab job [{$taskName}] run success now");
    }
}