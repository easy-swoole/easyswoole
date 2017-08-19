<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:06
 */

namespace Conf;


use Core\AbstractInterface\AbstractEvent;
use Core\AutoLoader;
use Core\Component\Di;
use Core\Component\Logger;
use Core\Component\Version\Control;
use Core\Http\Request;
use Core\Http\Response;
use Core\Swoole\SwooleHttpServer;
use Core\Swoole\Timer;

class Event extends AbstractEvent
{
    function frameInitialize()
    {
        // TODO: Implement frameInitialize() method.
        date_default_timezone_set('Asia/Shanghai');
        $loader = AutoLoader::getInstance();
        $loader->requireFile("App/Vendor/MysqliDb/MysqliDb.php");
        $loader->requireFile("App/Vendor/Smarty/Smarty.class.php");
    }

    function beforeWorkerStart(\swoole_http_server $server)
    {
        // TODO: Implement beforeWorkerStart() method.
        //udp 请勿用receive事件
        //添加自带多协议监听
        $udp = $server->addlistener("0.0.0.0",9502,SWOOLE_SOCK_UDP);
        $udp->on('packet',function(\swoole_server $server, $data,$addr){
            Logger::getInstance()->console("receive data {$data}");
            $server->sendto($addr['address'], $addr['port'], "Swoole: $data");
        });


//        $listener = $server->addlistener("0.0.0.0",9502,SWOOLE_TCP);
//        //混合监听tcp时    要重新设置包解析规则  才不会被HTTP覆盖，且端口不能与HTTP SERVER一致 HTTP本身就是TCP
//        $listener->set(array(
//            "open_eof_check"=>false,
//            "package_max_length"=>2048,
//        ));
//        $listener->on("connect",function(\swoole_server $server,$fd){
//            Logger::console("client connect");
//        });
//        $listener->on("receive",function(\swoole_server $server,$fd,$from_id,$data){
//            Logger::console("received connect");
//            $server->send($fd,"swoole ".$data);
//            $server->close($fd);
//        });
//        $listener->on("close",function (\swoole_server $server,$fd){
//            Logger::console("client close");
//        });



        //添加websocket回调事件
        $server->on("message",function (\swoole_websocket_server $server, \swoole_websocket_frame $frame){
            Logger::getInstance()->console("receive data".$frame->data);
            $server->push($frame->fd,"you say ".$frame->data);
        });

        $server->on("handshake",function (\swoole_http_request $request, \swoole_http_response $response){
            Logger::getInstance()->console("handshake");
            //自定定握手规则，没有设置则用系统内置的（只支持version:13的）
            if (!isset($request->header['sec-websocket-key']))
            {
                //'Bad protocol implementation: it is not RFC6455.'
                $response->end();
                return false;
            }
            if (0 === preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $request->header['sec-websocket-key'])
                || 16 !== strlen(base64_decode($request->header['sec-websocket-key']))
            )
            {
                //Header Sec-WebSocket-Key is illegal;
                $response->end();
                return false;
            }

            $key = base64_encode(sha1($request->header['sec-websocket-key']
                . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
                true));
            $headers = array(
                'Upgrade'               => 'websocket',
                'Connection'            => 'Upgrade',
                'Sec-WebSocket-Accept'  => $key,
                'Sec-WebSocket-Version' => '13',
                'KeepAlive'             => 'off',
            );
            foreach ($headers as $key => $val)
            {
                $response->header($key, $val);
            }
            $response->status(101);
            $response->end();
            SwooleHttpServer::getInstance()->getServer()->push($request->fd,"hello world,your fd is ".$request->fd);
        });

        $server->on("close",function (\swoole_http_server $server,$fd){
            $info =  SwooleHttpServer::getInstance()->getServer()->connection_info($fd);
            if($info['websocket_status']){
                Logger::getInstance()->console("websocket client {$fd} close");
            }
        });
        //添加websocket回调事件结束




    }

    function onStart(\swoole_http_server $server)
    {
        // TODO: Implement onStart() method.
        //使用event loop实现自定义 socket监听
        $listener = stream_socket_server(
            "udp://0.0.0.0:9503",
            $error,
            $errMsg,
            STREAM_SERVER_BIND
        );
        if($errMsg){
            throw new \Exception("listen fail");
        }else{
            //加入event loop
            swoole_event_add($listener,function($listener){
                $data = stream_socket_recvfrom($listener,9503,0,$client);
                Logger::getInstance()->console("rec data {$data} in event loop");
                stream_socket_sendto($listener,"hello this is event loop",0,$client);
            });
        }
    }

    function onShutdown(\swoole_http_server $server)
    {
        // TODO: Implement onShutdown() method.
    }

    function onWorkerStart(\swoole_server $server, $workerId)
    {
        // TODO: Implement onWorkerStart() method.
        //为第一个worker添加一个定时器
        if($workerId == 0){
            //10秒
            Timer::loop(10*1000,function (){
                Logger::getInstance()->console("this is timer");
            });
        }

    }

    function onWorkerStop(\swoole_server $server, $workerId)
    {
        // TODO: Implement onWorkerStop() method.
    }

    function onRequest(Request $request, Response $response)
    {
        // TODO: Implement onRequest() method.
    }

    function onDispatcher(Request $request, Response $response, $targetControllerClass, $targetAction)
    {
        // TODO: Implement onDispatcher() method.
    }

    function onResponse(Request $request,Response $response)
    {
        // TODO: Implement afterResponse() method.
    }

    function onTask(\swoole_http_server $server, $taskId, $workerId, $taskObj)
    {
        // TODO: Implement onTask() method.
    }

    function onFinish(\swoole_http_server $server, $taskId, $taskObj)
    {
        // TODO: Implement onFinish() method.
    }

    function onWorkerError(\swoole_http_server $server, $worker_id, $worker_pid, $exit_code)
    {
        // TODO: Implement onWorkerError() method.
    }
}
