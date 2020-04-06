<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Singleton;
use Swoole\Server;

class Bridge
{
    use Singleton;


    private $onStart;
    private $onCommand;

    function __construct()
    {
        $this->onCommand = new BridgeCommand();
    }

    function onCommand():BridgeCommand
    {
        return $this->onCommand;
    }

    function send(Bridge $bridge):Package
    {

    }

    function __attachServer(Server $server)
    {

    }

}