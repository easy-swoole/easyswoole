<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Socket\Tools\Protocol;
use Swoole\Coroutine\Socket;

class BridgeProcess extends AbstractUnixProcess
{
    function onAccept(Socket $socket)
    {
        $data = Protocol::socketReader($socket, 3, false);
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
            $package = new  Package();
            $package->setStatus(Package::STATUS_COMMAND_ERROR);
            Protocol::socketWriter($socket, serialize($package));
            $socket->close();
            return null;
        }

        $data = $callback($package);
        $package = new Package();
        $package->setStatus($package::STATUS_SUCCESS);
        $package->setArgs($data);

        Protocol::socketWriter($socket,serialize($package));
        $socket->close();
        return null;
    }


    protected function onException(\Throwable $throwable, ...$args)
    {
        Trigger::getInstance()->throwable($throwable);
    }
}
