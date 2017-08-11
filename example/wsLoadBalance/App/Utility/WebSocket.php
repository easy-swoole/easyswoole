<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 16/8/10
 * Time: 15:00
 */

namespace App\Utility;


use Conf\Config;
use Conf\Event;
use Core\Swoole\SwooleHttpServer;

class WebSocket {
    private static $instance;
    public static $local_ip = '';
    protected $fd;
    protected $uid;
    static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }
    function setClientInfo($uid,$fd){
        $this->fd = $fd ;
        $this->uid = $uid ;
    }
    /**
     * @param \swoole_http_server $server
     * 绑定ws回调
     */
    public function build(\swoole_http_server $server){
        //获取本机ip
        $ip = \swoole_get_local_ip();
        self::$local_ip = $ip['eth0'];
        $server->on("open",[$this, 'onOpen']);
        $server->on("message",[$this, 'onMessage']);
        $server->on("close",[$this, 'onClose']);
        //监听udp包，用来获取其他服务器发来的消息
        $udp = $server->addlistener("0.0.0.0",9502,SWOOLE_SOCK_UDP);
        $udp->on('packet',[$this, 'onPacket']);
    }
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $req){
        //生一个唯一的数字，当做当前登录用户的id
        $uid = crc32(md5(uniqid()));
        $this->send($req->fd,['type'=>'welcome','id'=>$uid]);
        $server->bind($req->fd,$uid);
        //把链接信息存储到redis里边
        Redis::getInstance()->hmset('user_fd_list',[$uid => json_encode(['ip'=>self::$local_ip,'fd'=>$req->fd])]);
    }
    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame){
        //收到消息
        $info =  $server->connection_info($frame->fd);
        $this->setClientInfo($info['uid'],$frame->fd);
        $this->handle($frame->data);
    }
    public function onClose(\swoole_http_server $server,$fd){
        $info =  $server->connection_info($fd);
        if($info['websocket_status']){
            //用户连接断开，通知所有用户
            $this->sendToAll(['type'=>'closed', 'id'=>$info['uid']]);
            Redis::getInstance()->hDel('user_fd_list',$info['uid']);
        }
    }
    public function onPacket(\swoole_server $server, $data,$addr){
        $data = json_decode($data,true);
        //去除索要发送用户的句柄信息
        $userInfo = Redis::getInstance()->hmget('user_fd_list',[$data['touid']]);
        $userInfo = json_decode($userInfo[$data['touid']],true);
        unset($data['touid']);
        $this->send($userInfo['fd'],$data);
    }
    /**
     * @param $data
     * 收到消息并解析
     */
    public function handle($data){
        $data = json_decode($data,true);
        switch($data['type']){
            case 'update':
                //更新当前用户位置信息
                $this->sendToAll([
                        'type'     => 'update',
                        'id'       => $this->uid,
                        'angle'    => floatval($data["angle"]),
                        'momentum' => floatval($data["momentum"]),
                        'x'        => floatval($data["x"]),
                        'y'        => floatval($data["y"]),
                        'name'     => isset($data['name']) ? $data['name'] : $this->uid,
                        'authorized'  => false,
                ]);
                break;
            case 'message':
                // 群发消息
                $new_message = [
                    'type'=>'message',
                    'id'  =>$this->uid,
                    'message'=>$data['message'],
                ];
                $this->sendToAll($new_message);
                break;
        }
    }
    /**
     * @param $msg
     * 发送信息给所有用户
     */
    public function sendToAll($msg){
        $user_list = Redis::getInstance()->hgetall('user_fd_list');
        //print_r($this->uid . PHP_EOL);
        foreach ($user_list as $k => $v){
            $userInfo = json_decode($v,true);
            if($this->uid == $k){
                continue;
            }
            if($v['ip'] != self::$local_ip){
                SwooleHttpServer::getInstance()->getServer()->sendto($userInfo['ip'],9502,json_encode(array_merge($msg,['touid'=>$k])));
                continue;
            }
            //print_r($v);
            $this->send($userInfo['fd'],$msg);
        }
    }

    /**
     * @param $fd
     * @param $data
     * 向客户端发送消息
     */
    public function send($fd,$data){
        SwooleHttpServer::getInstance()->getServer()->push($fd,json_encode($data));
    }
}