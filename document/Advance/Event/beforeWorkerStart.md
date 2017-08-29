# 服务启动前事件
在执行该事件时，框架已经完成的工作有：
- frameInitialize 事件内的全部事务
- 系统临时目录与日志目录的建立
- 错误处理函数的注册
- swoole_http_server对象创建，且设置了启动参数。（未启动）

在该回调事件内，依旧可以进行一些全局设置和对象创建,同时可以对Swoole Server进行一些个性化的需求挖掘。

## 往DI容器中注入一个全局对象。
```
Di::getInstance()->set("dbClass",new DbClass());
```
## 为Swoole Http Server添加web Socket支持
```
$server->on("message",function (\swoole_websocket_server $server, \swoole_websocket_frame $frame){
            Logger::console("receive data".$frame->data);
            $server->push($frame->fd,"you say ".$frame->data);
        });
        $server->on("handshake",function (\swoole_http_request $request, \swoole_http_response $response){
            Logger::console("handshake");
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
//            SwooleHttpServer::getInstance()->getServer()->push($request->fd,"hello world");
        });
        $server->on("close",function ($ser,$fd){
            Logger::console("client {$fd} close");
        });
```
## 监听TCP链接
```
$listener = $server->addlistener("0.0.0.0",9502,SWOOLE_TCP);
        //混合监听tcp时    要重新设置包解析规则  才不会被HTTP覆盖，且端口不能与HTTP SERVER一致 HTTP本身就是TCP
        $listener->set(array(
            "open_eof_check"=>false,
            "package_max_length"=>2048,
        ));
        $listener->on("connect",function(\swoole_server $server,$fd){
            Logger::console("client connect");
        });
        $listener->on("receive",function(\swoole_server $server,$fd,$from_id,$data){
            Logger::console("received connect");
            $server->send($fd,"swoole ".$data);
            $server->close($fd);
        });
        $listener->on("close",function (\swoole_server $server,$fd){
            Logger::console("client close");
        });
```
## 监听UDP链接
```
$udp = $server->addlistener("0.0.0.0",9503,SWOOLE_UDP);
        //udp 请勿用receive事件
        $udp->on('packet',function(\swoole_server $server, $data,$clientInfo){
            var_dump($data);
        });
```
## 使用Event Loop监听UDP
```
        $listener = stream_socket_server(
            "udp://0.0.0.0:9504",
            $error,
            $errMsg,
            STREAM_SERVER_BIND
        );
        if($errMsg){
            throw new \Exception("cluster server bind error on msg :{$errMsg}");
        }else{
            //加入event loop
            swoole_event_add($listener,function($listener){
                $data = stream_socket_recvfrom($listener,9504,0,$client);
                var_dump($data);
                stream_socket_sendto($listener,"hello",0,$client());
            });
        }

```
> 注意：TCP同理。

## 添加自定义Process进程
```
$server->addProcess(new \swoole_process(function (){
            while (1){
                /*
                 * for example,you can set a redis,kafka or rabbitMQ client
                 * here,and read the message queue in blocking mode
                */
                sleep(1);
                Logger::getInstance()->console("my process run");
            }
        })
     );
```
## 利用iNotify实现Server热重启
```
//请确定有inotify拓展
        $a = function ($dir)use(&$a){
            $data = array();
            if(is_dir($dir)){
                //是目录的话，先增当前目录进去
                $data[] = $dir;
                $files = array_diff(scandir($dir), array('.', '..'));
                foreach ($files as $file){
                    $data = array_merge($data ,$a($dir."/".$file));
                }
            }else{
                $data[] = $dir;
            }
            return $data;
        };
        $list = $a(ROOT."/App");
        $notify = inotify_init();
        foreach ($list as $item){
            inotify_add_watch($notify, $item,IN_CREATE | IN_DELETE|IN_MODIFY);
        }
        swoole_event_add($notify,function()use($notify){
            $events = inotify_read($notify);
            if(!empty($events)){
                //注意更新多个文件的间隔时间处理,防止一次更新了10个文件，重启了10次，懒得做了，反正原理在这里
                SwooleHttpServer::getInstance()->getServer()->reload();
            }
        });
```
