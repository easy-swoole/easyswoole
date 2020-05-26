<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\CommandInterface;
use EasySwoole\Bridge\Package;
use Swoole\Coroutine\Socket;
use EasySwoole\EasySwoole\Crontab\Crontab as EasySwooleCron;

class Crontab implements CommandInterface
{
    public function commandName(): string
    {
        return 'crontab';
    }

    public function exec(Package $package, Package $responsePackage, Socket $socket)
    {
        $action = $package->getArgs()['action'] ?? '';
        if (!method_exists($this, $action)) {
            $responsePackage->setStatus($responsePackage::STATUS_COMMAND_NOT_EXIST);
            $responsePackage->setMsg("command action:{$action} not empty");
            return $responsePackage;
        }
        $this->$action($package, $responsePackage);
    }


    function show(Package $package, Package $response)
    {
        $info = EasySwooleCron::getInstance()->infoTable();
        $data = [];
        foreach ($info as $k => $v) {
            $data[$k] = $v;
        }
        $response->setArgs($data);
    }

    function stop(Package $package, Package $response)
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

    function resume(Package $package, Package $response)
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

    function run(Package $package, Package $response)
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
