<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/2
 * Time: 下午10:31
 */

namespace EasySwoole\Core\Swoole;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Event;

class Server
{
    use Singleton;
    private $swooleServer;
    private $conf;
    public function __construct()
    {
        $conf = Config::getInstance();
        $this->conf = $conf;
        if($conf->getServerType() == Config::TYPE_SERVER){
            $this->swooleServer = new \swoole_server($conf->getListenIp(),$conf->getListenPort(),$conf->getSocketType());
        }else if($conf->getServerType() == Config::TYPE_WEB){
            $this->swooleServer = new \swoole_http_server($conf->getListenIp(),$conf->getListenPort());
        }else if($conf->getServerType() == Config::TYPE_WEB_SOCKET){
            $this->swooleServer = new \swoole_websocket_server($conf->getListenIp(),$conf->getListenPort());
        }else{
            die('server type error');
        }
        Event::serverCreate($this->swooleServer);
    }

    public function getServer():\swoole_server
    {
        return $this->swooleServer;
    }

    public function start()
    {
        $this->getServer()->set($this->getConf()->getWorkerSetting());
        $register = new EventRegister();
        Event::swooleEventRegister($register);
        $events = $register->all();
        foreach ($events as $event => $callback){
            $this->getServer()->on($event,$callback);
        }
        $this->getServer()->start();
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

    public function getConf():Config
    {
        return $this->conf;
    }

}