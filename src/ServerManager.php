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
use Swoole\Server;
use Swoole\WebSocket\Server as WebSocketServer;
use Swoole\Http\Server as HttpServer;

class ServerManager
{
    use Singleton;
    /**
     * @var Server $swooleServer
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
     * @return null|Server|Server\Port|WebSocketServer|HttpServer
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
                $this->swooleServer = new Server($address,$port,...$args);
                break;
            }
            case EASYSWOOLE_WEB_SERVER:{
                $this->swooleServer = new HttpServer($address,$port,...$args);
                break;
            }
            case EASYSWOOLE_WEB_SOCKET_SERVER:{
                $this->swooleServer = new WebSocketServer($address,$port,...$args);
                break;
            }
            default:{
                Trigger::getInstance()->error("unknown server type :{$type}");
                return false;
            }
        }
        if($this->swooleServer){
            $this->swooleServer->set($setting);
        }
        return true;
    }


    public function addServer(string $serverName,int $port,int $type = SWOOLE_TCP,string $listenAddress = '0.0.0.0',array $setting = []):EventRegister
    {
        $eventRegister = new EventRegister();
        $subPort = $this->swooleServer->addlistener($listenAddress,$port,$type);
        if(!empty($setting)){
            $subPort->set($setting);
        }
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

    function getEventRegister(string $serverName = null):?EventRegister
    {
        if($serverName === null){
            return $this->mainServerEventRegister;
        }else if(isset($this->subServerRegister[$serverName])){
            return $this->subServerRegister[$serverName];
        }
        return null;
    }

    function start()
    {
        //注册主服务事件回调
        $events = $this->getEventRegister()->all();
        foreach ($events as $event => $callback){
            $this->getSwooleServer()->on($event, function (...$args) use ($callback) {
                foreach ($callback as $item) {
                    call_user_func($item,...$args);
                }
            });
        }
        //注册子服务的事件回调
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
        $this->isStart = true;
        $this->getSwooleServer()->start();
    }

    function isStart():bool
    {
        return $this->isStart;
    }
}
