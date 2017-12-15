<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/12
 * Time: 下午6:03
 */

namespace EasySwoole\Core\Swoole;
use EasySwoole\Config;
use EasySwoole\Event;

class ServerManager
{
    private static $instance;
    private $serverList = [];
    private $mainServer = null;

    const TYPE_SERVER = 1;
    const TYPE_WEB_SERVER = 2;
    const TYPE_WEB_SOCKET_SERVER = 3;

    public static function getInstance():ServerManager
    {
        if(!isset(self::$instance)){
            self::$instance = new ServerManager();
        }
        return ServerManager::$instance;
    }

    public function addServer(string $serverName,int $port,int $type,string $host = '0.0.0.0',array $setting = null):EventRegister
    {
        $eventRegister = new EventRegister();
        $this->serverList[$serverName] = [
            'port'=>$port,
            'host'=>$host,
            'type'=>$type,
            'setting'=>$setting,
            'eventRegister'=>$eventRegister
        ];
        return $eventRegister;
    }

    public function start():void
    {
       $this->createMainServer();
       $this->attachListener();
       $this->getServer()->start();
    }


    private function attachListener():void
    {
        $mainServer = $this->getServer();
        foreach ($this->serverList as $serverName => $server){
            $subPort = $mainServer->addlistener($server['host'],$server['port'],$server['type']);
            if($subPort){
                if(is_array($server['setting'])){
                    $subPort->set($server['setting']);
                }
                $events = $server['eventRegister']->all();
                foreach ($events as $event => $callback){
                    $subPort->on($event,$callback);
                }
            }else{
                throw new \Exception("addListener with server name:{$serverName} at host:{$server['host']} port:{$server['port']} fail");
            }
        }
    }

    private function createMainServer():\swoole_server
    {
        $conf = Config::getInstance()->getConf("MAIN_SERVER");
        $runModel = $conf['RUN_MODEL'];
        $host = $conf['HOST'];
        $port = $conf['PORT'];
        $setting = $conf['SETTING'];
        $sockType = $conf['SOCK_TYPE'];
        switch ($conf['SERVER_TYPE']){
            case self::TYPE_SERVER:{
                $this->mainServer = new \swoole_server($host,$port,$runModel,$sockType);
                break;
            }
            case self::TYPE_WEB_SERVER:{
                $this->mainServer = new \swoole_http_server($host,$port,$runModel);
                break;
            }
            case self::TYPE_WEB_SOCKET_SERVER:{
                $this->mainServer = new \swoole_websocket_server($host,$port,$runModel);
                break;
            }
            default:{
                throw new \Exception("unknown server type ");
            }
        }
        $this->mainServer->set($setting);
        //创建默认的事件注册器
        $register = new EventRegister();
        Event::mainServerCreate($this,$register);
        //检查是否注册了默认的ontask与onfinish事件
        if(!$register->get($register::onTask)){
            $register->registerDefaultOnTask();
        }
        if(!$register->get($register::onFinish)){
            $register->registerDefaultOnFinish();
        }
        if($conf['SERVER_TYPE'] == self::TYPE_WEB_SERVER){
            //检查是否注册了onRequest
            if(!$register->get($register::onRequest)){
                $register->registerDefaultOnRequest();
            }
        }
        $events = $register->all();
        foreach ($events as $event => $callback){
            $this->mainServer->on($event,$callback);
        }
        return $this->mainServer;
    }


    public function getServer():?\swoole_server
    {
         if($this->mainServer){
             return $this->mainServer;
         }else{
             throw  new \Exception('getServer cannot call before mainServer create');
         }
    }


    public function coroutineId():?int
    {
        if(class_exists('Swoole\Coroutine')){
            //进程错误的时候返回-1
            $ret =  \Swoole\Coroutine::getuid();
            if($ret >= 0){
                return $ret;
            }else{
                return null;
            }
        }else{
            return null;
        }
    }

    public function isCoroutine():bool
    {
        if($this->coroutineId() !== null){
            return true;
        }else{
            return false;
        }
    }
}