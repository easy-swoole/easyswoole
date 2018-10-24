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
        $authKey = Config::getInstance()->getConf('CONSOLE.AUTH');
        //如果开启了权限验证
        if(!empty($authKey)){
            $fd = $this->caller()->getClient()->getFd();
            $info = TableManager::getInstance()->get('Console.Auth')->get($fd);
            //如果是执行鉴权命令
            if($actionName == 'auth'){
                return true;
            }else{
                //执行非鉴权命令的时候
                if(!empty($info)){
                    if($info['isAuth']){
                        return true;
                    }else{
                        $this->response()->setMessage('please enter your auth key; auth $authKey');
                        return false;
                    }
                }else{
                    $this->response()->setMessage('please enter your auth key; auth $authKey');
                    return false;
                }
            }


        }else{
            return true;
        }
    }


    function auth()
    {
        $fd = $this->caller()->getClient()->getFd();
        $args = $this->caller()->getArgs();
        if(Config::getInstance()->getConf('CONSOLE.AUTH') == array_shift($args)){
            TableManager::getInstance()->get('Console.Auth')->set($fd,[
                'isAuth'=>1,
                'tryTimes'=>0
            ]);
            $this->response()->setMessage('auth succeed');
        }else{
            $info = TableManager::getInstance()->get('Console.Auth')->get($fd);
            if(!empty($info)){
                if($info['tryTimes'] > 5){
                    $this->response()->setStatus(Response::STATUS_RESPONSE_AND_CLOSE);
                }else{
                    TableManager::getInstance()->get('Console.Auth')->set($fd,[
                        'isAuth'=>0,
                        'tryTimes'=>$info['tryTimes']+1
                    ]);
                }
            }else{
                TableManager::getInstance()->get('Console.Auth')->set($fd,[
                    'isAuth'=>0,
                    'tryTimes'=>1
                ]);
            }
            $this->response()->setMessage('auth fail');
        }
    }

    protected function actionNotFound(?string $actionName)
    {
        $call = CommandContainer::getInstance()->get($actionName);
        if(is_callable($call)){
            CommandContainer::getInstance()->hook($actionName,$this->caller(),$this->response());
        }else{
            $this->response()->setMessage("action {$actionName} miss");
        }
    }

    function hostIp()
    {
        $list = swoole_get_local_ip();
        $this->response()->setMessage($this->arrayToString($list));
    }

    function status()
    {
        $this->response()->setMessage($this->arrayToString(ServerManager::getInstance()->getSwooleServer()->stats()));
    }

    function reload()
    {
        ServerManager::getInstance()->getSwooleServer()->reload();
        $this->response()->setMessage('reload at'.time());
    }

    function shutdown()
    {
        ServerManager::getInstance()->getSwooleServer()->shutdown();
        $this->response()->setMessage('shutdown at'.time());
    }

    function clientInfo()
    {
        $args = $this->caller()->getArgs();
        $fd = array_shift($args);
        if(!empty($fd)){
            $info = ServerManager::getInstance()->getSwooleServer()->getClientInfo($fd);
        }else{
            $info = [];
        }
        $this->response()->setMessage($this->arrayToString($info));
    }

    function close(){
        $this->response()->setMessage("Bye Bye !");
        $this->response()->setStatus(Response::STATUS_RESPONSE_AND_CLOSE);
    }

    function test(){
        new AAA();
    }

    private function arrayToString(array $array):string
    {
        $str = '';
        foreach ($array as $key => $value){
            $str .= "{$key} : {$value} \n";
        }
        return trim($str);
    }
}