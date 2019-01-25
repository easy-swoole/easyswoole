<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/20
 * Time: 下午11:27
 */

namespace EasySwoole\EasySwoole\Console;


use EasySwoole\Socket\AbstractInterface\Controller;

class ConsoleTcpController extends Controller
{
    function onRequest(?string $actionName): bool
    {
        $fd = $this->caller()->getClient()->getFd();
        if($actionName == 'auth'){
            return true;
        }else{
            foreach (ConsoleService::getInstance()->authTable as $key => $value){
                if($value['fd'] === $fd){
                    $modules = unserialize( $value['modules']);
                    $this->caller()->user = $value['user'];
                    if(in_array($actionName,$modules)){
                        return true;
                    }
                }
            }
        }
        $this->response()->setMessage('you have no permission to '.$actionName.' module');
        return false;
    }

    /**
     * 控制器本身不再处理任何实体action
     * 全部转发给对应注册的命令处理器进行处理
     * @param null|string $actionName
     * @author: eValor < master@evalor.cn >
     */
    protected function actionNotFound(?string $actionName)
    {
        ModuleContainer::getInstance()->hook($actionName, $this->caller(), $this->response());
    }
}