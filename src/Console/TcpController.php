<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/20
 * Time: 下午11:27
 */

namespace EasySwoole\EasySwoole\Console;


use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Swoole\Memory\TableManager;
use EasySwoole\Socket\AbstractInterface\Controller;
use EasySwoole\Socket\Bean\Response;

class TcpController extends Controller
{
    function onRequest(?string $actionName): bool
    {
        $fd = $this->caller()->getClient()->getFd();
        $authKey = Config::getInstance()->getConf('CONSOLE.AUTH');
        //如果开启了权限验证
        if (!empty($authKey)) {
            $info = TableManager::getInstance()->get('Console.Auth')->get($fd);
            //如果是执行鉴权命令
            if ($actionName == 'auth') {
                return true;
            } else {
                //执行非鉴权命令的时候
                if (!empty($info)) {
                    if ($info['isAuth']) {
                        return true;
                    } else {
                        $this->response()->setMessage('please enter your auth key; auth $authKey');
                        return false;
                    }
                } else {
                    $this->response()->setMessage('please enter your auth key; auth $authKey');
                    return false;
                }
            }
        } else {
            return true;
        }
    }

    /**
     * 控制器本身不再处理任何实体action
     * 全部转发给对应注册的命令处理器进行处理
     * @param null|string $actionName
     * @author: eValor < master@evalor.cn >
     */
    protected function actionNotFound(?string $actionName)
    {
        CommandContainer::getInstance()->hook($actionName, $this->caller(), $this->response());
    }
}