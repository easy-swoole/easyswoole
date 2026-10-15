<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Bridge\CommandInterface;
use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;


abstract class AbstractCommand implements CommandInterface
{
    private const forbidList = [
        'exec', 'commandname',
        '__hook', '__construct', '__destruct', '__clone',
        '__call', '__callstatic', '__get', '__set', '__isset', '__unset',
        '__sleep', '__wakeup', '__serialize', '__unserialize',
        '__tostring', '__invoke', '__set_state', '__debuginfo',
    ];

    public function exec(Package $request, Package $responsePackage)
    {
        $args = $request->getArgs();
        if (!is_array($args) && $args !== null) {
            $responsePackage->setStatus(StatusEnum::COMMAND_EXEC_ERROR);
            $responsePackage->setMsg('invalid bridge arguments: expected an array');
            return;
        }
        $action = $args['action'] ?? '';
        if (!is_string($action)) {
            $responsePackage->setStatus(StatusEnum::COMMAND_EXEC_ERROR);
            $responsePackage->setMsg('invalid bridge action: expected a string');
            return;
        }
        $class = new \ReflectionClass($this);
        $method = $class->hasMethod($action) ? $class->getMethod($action) : null;
        if (in_array(strtolower($action), self::forbidList, true)
            || $method === null
            || !$method->isPublic()
            || $method->isStatic()) {
            $responsePackage->setStatus(StatusEnum::COMMAND_EXEC_ERROR);
            $responsePackage->setMsg("baseService bridge command [{$action}] not exists");
            return;
        }
        $method->invoke($this, $request, $responsePackage);
    }
}