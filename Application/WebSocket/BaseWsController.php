<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2018/3/4
 * Time: 0:10
 */

namespace App\WebSocket;


use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Spl\SplStream;
use EasySwoole\Core\Socket\AbstractInterface\WebSocketController;
use EasySwoole\Core\Socket\Client\WebSocket;
use EasySwoole\Core\Socket\Common\CommandBean;

class BaseWsController extends WebSocketController
{
    /**
     * @var \Redis
     */
    protected $redis;

    function __construct(WebSocket $client, CommandBean $request, SplStream $response)
    {
        $this->redis = Di::getInstance()->get("REDIS")->getConnect();
        parent::__construct($client, $request, $response);
    }

    function actionNotFound(?string $actionName)
    {
        $this->response()->write("action call {$actionName} not found");
    }


}