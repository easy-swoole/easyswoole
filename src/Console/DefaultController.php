<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/20
 * Time: 下午11:27
 */

namespace EasySwoole\EasySwoole\Console;


use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Socket\AbstractInterface\Controller;
use EasySwoole\Socket\Bean\Response;

class DefaultController extends Controller
{
    protected function actionNotFound(?string $actionName)
    {
        $this->response()->addResult('msg',"action {$actionName}@class ".static::class." miss");
    }

    function hostIp()
    {
        $this->response()->addResult('ipList',swoole_get_local_ip());
    }

    function status()
    {
        $this->response()->addResult('status',ServerManager::getInstance()->getSwooleServer()->stats());
    }

    function reload()
    {
        $this->response()->addResult('reloadTime',time());
    }

    function shutdown()
    {
        ServerManager::getInstance()->getSwooleServer()->shutdown();
        $this->response()->addResult('shutdown',time());
    }

    function clientInfo()
    {
        $args = $this->caller()->getArgs();
        if(isset($args['fd'])){
            $info = ServerManager::getInstance()->getSwooleServer()->getClientInfo($args['fd']);
        }else{
            $info = null;
        }
        $this->response()->addResult('clientInfo',$info);
    }
}