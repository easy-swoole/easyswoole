<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/28
 * Time: 下午5:36
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Swoole\EventRegister;

class ServerManager
{
    use Singleton;
    /**
     * @var \swoole_server $swooleServer
     */
    private $swooleServer;
    private $mainServerEventRegister;
    private $subServer = [];
    private $subServerRegister = [];
    private $isStart = false;

    function __construct()
    {
        $this->mainServerEventRegister = new EventRegister();
    }
    /**
     * @param string $serverName
     * @return null|\swoole_server|\swoole_server_port|\swoole_websocket_server|\swoole_http_server
     */
    function getSwooleServer(string $serverName = null)
    {
        if($serverName === null){
            return $this->swooleServer;
        }else{
            if(isset($this->subServer[$serverName])){
                return $this->subServer[$serverName];
            }
            return null;
        }
    }

    function createSwooleServer($port,$type ,$address = '0.0.0.0',array $setting = [],...$args):bool
    {
        switch ($type){
            case EASYSWOOLE_SERVER:{
                $this->swooleServer = new \swoole_server($address,$port,...$args);
                break;
            }
            case EASYSWOOLE_WEB_SERVER:{
                $this->swooleServer = new \swoole_http_server($address,$port,...$args);
                break;
            }
            case EASYSWOOLE_WEB_SOCKET_SERVER:{
                $this->swooleServer = new \swoole_websocket_server($address,$port,...$args);
                break;
            }
            default:{
                Trigger::getInstance()->error('"unknown server type :{$type}"');
                return false;
            }
        }
        if($this->swooleServer){
            $this->swooleServer->set($setting);
        }
        return true;
    }


    public function addServer(string $serverName,int $port,int $type = SWOOLE_TCP,string $listenAddress = '0.0.0.0',array $setting = [
        "open_eof_check"=>false,
    ]):EventRegister
    {
        $eventRegister = new EventRegister();
        $subPort = $this->swooleServer->addlistener($listenAddress,$port,$type);
        $subPort->set($setting);
        $this->subServer[$serverName] = $subPort;
        $this->subServerRegister[$serverName] = [
            'port'=>$port,
            'listenAddress'=>$listenAddress,
            'type'=>$type,
            'setting'=>$setting,
            'eventRegister'=>$eventRegister
        ];
        return $eventRegister;
    }

    function getMainEventRegister():EventRegister
    {
        return $this->mainServerEventRegister;
    }

    function start()
    {
        $events = $this->getMainEventRegister()->all();
        foreach ($events as $event => $callback){
            $this->getSwooleServer()->on($event, function (...$args) use ($callback) {
                foreach ($callback as $item) {
                    call_user_func($item,...$args);
                }
            });
        }
        $this->registerSubPortCallback();
        $this->isStart = true;
        $this->getSwooleServer()->start();
    }

    function isStart():bool
    {
        return $this->isStart;
    }

    private function registerSubPortCallback():void
    {
        foreach ($this->subServer as $serverName => $subPort ){
            $events = $this->subServerRegister[$serverName]['eventRegister']->all();
            foreach ($events as $event => $callback){
                $subPort->on($event, function (...$args) use ($callback) {
                    foreach ($callback as $item) {
                        call_user_func($item,...$args);
                    }
                });
            }
        }
    }

    function getSubServerRegister():array
    {
        return $this->subServerRegister;
    }
}
