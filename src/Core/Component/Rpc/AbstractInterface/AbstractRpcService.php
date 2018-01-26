<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/22
 * Time: 下午12:51
 */

namespace EasySwoole\Core\Component\Rpc\AbstractInterface;


use EasySwoole\Core\Component\Rpc\Common\Status;
use EasySwoole\Core\Socket\TcpController;
use EasySwoole\Core\Swoole\ServerManager;

abstract class AbstractRpcService extends TcpController
{
    function actionNotFound(string $actionName)
    {
        $this->response()->setError("action : {$actionName} not found");
        $this->response()->setStatus(Status::ACTION_NOT_FOUND);
    }

    function onException(\Throwable $throwable): void
    {
        $this->response()->setError($throwable->getMessage());
        $this->response()->setStatus(Status::SERVER_ERROR);
    }
}