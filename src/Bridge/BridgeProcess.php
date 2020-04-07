<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Socket\Tools\Protocol;
use Swoole\Coroutine\Socket;

class BridgeProcess extends AbstractUnixProcess
{
    function run($arg)
    {
        $onStart = $arg['onStart'];
        if($onStart){
            call_user_func($onStart,$this);
        }
        parent::run($arg);
    }

    function onAccept(Socket $socket)
    {
        $data = Protocol::socketReader($socket, 3);
        if ($data === null) {
            $package = new  Package();
            $package->setStatus(Package::STATUS_PACKAGE_ERROR);
            Protocol::socketWriter($socket, serialize($package));
            $socket->close();
            return null;
        }
        /**
         * @var $package Package
         */
        $package = unserialize($data);
        $callback = Bridge::getInstance()->onCommand()->get($package->getCommand());
        if (!$callback) {
            $package = new Package();
            $package->setStatus(Package::STATUS_COMMAND_NOT_EXIST);
            $package->setArgs("command:{$package->getCommand()} is not exist");
            Protocol::socketWriter($socket, serialize($package));
            $socket->close();
            return null;
        }
        $responsePackage = new Package();
        try{
            //结果在闭包中更改
            $responsePackage->setStatus(Package::STATUS_SUCCESS);
            call_user_func($callback,$package,$responsePackage,$socket);
        }catch (\Throwable $throwable){
            $responsePackage->setStatus(Package::STATUS_COMMAND_ERROR);
            $responsePackage->setArgs($throwable->getMessage());
            $this->onException($throwable);
        } finally {
            Protocol::socketWriter($socket,serialize($responsePackage));
            $socket->close();
        }
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        Trigger::getInstance()->throwable($throwable);
    }
}
