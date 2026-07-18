<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Bridge\CommandInterface;
use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;


abstract class AbstractCommand implements CommandInterface
{
    public function exec(Package $request, Package $responsePackage)
    {
        $action = $request->getArgs()['action'] ?? '';
        if (!method_exists($this, $action)) {
            $responsePackage->setStatus(StatusEnum::COMMAND_EXEC_ERROR);
            $responsePackage->setMsg("baseService bridge command [{$action}] not exists");
            return;
        }
        $this->{$action}($request, $responsePackage);
    }
}