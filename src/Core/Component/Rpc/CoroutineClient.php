<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/10
 * Time: 下午12:25
 */

namespace EasySwoole\Core\Component\Rpc;


use EasySwoole\Core\Component\Invoker;
use EasySwoole\Core\Component\Openssl;
use EasySwoole\Core\Component\Rpc\Client\TaskObj;
use EasySwoole\Core\Component\Rpc\Common\Parser;
use EasySwoole\Core\Component\Rpc\Common\ServiceCaller;
use EasySwoole\Core\Component\Rpc\Client\ServiceResponse;
use EasySwoole\Core\Component\Rpc\Common\Status;
use EasySwoole\Core\Component\Rpc\Common\ServiceNode;
use EasySwoole\Core\Component\Trigger;


class CoroutineClient
{
    private $taskList = [];

    private $clientConnectTimeOut = 0.1;

    function addCall(string $serviceName,string $serviceGroup,string $action,...$args)
    {
        $obj = new TaskObj();
        $obj->setServiceName($serviceName);
        $obj->setServiceAction($action);
        $obj->setServiceGroup($serviceGroup);
        $obj->setArgs($args);
        $this->taskList[] = $obj;
        return $obj;
    }

    function call($timeOut = 0.1)
    {

    }
}