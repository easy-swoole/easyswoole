# EasySwoole
```
  ______                          _____                              _        
 |  ____|                        / ____|                            | |       
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___ 
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |                                               
                         |___/                                                
```
# EasySwoole

EasySwoole 是一款基于Swoole Server 开发的常驻内存型PHP框架，专为API而生，摆脱传统PHP运行模式在进程唤起和文件加载上带来的性能损失。EasySwoole 高度封装了 Swoole Server 而依旧维持 Swoole Server 原有特性，支持同时混合监听HTTP、自定义TCP、UDP协议，让开发者以最低的学习成本和精力编写出多进程，可异步，高可用的应用服务

## 特性

- 强大的 TCP/UDP Server 框架，多线程，EventLoop，事件驱动，异步，Worker进程组，Task异步任务，毫秒定时器，SSL/TLS隧道加密
- EventLoop API，让用户可以直接操作底层的事件循环，将socket，stream，管道等Linux文件加入到事件循环中

## 优势

- 简单易用开发效率高
- 并发百万TCP连接
- TCP/UDP/UnixSock
- 支持异步/同步/协程
- 支持多进程/多线程
- CPU亲和性/守护进程

## 基准测试

使用阿里云 **1核1G** 未做任何内核优化的实例作为运行 **easySwoole** 的测试机器，同时内网环境下部署另一台未经任何优化的施压机，详细配置如下

|   配置   |    测试机     |    施压机     |
| :----: | :--------: | :--------: |
|  操作系统  | CentOS 7.4 | CentOS 7.4 |
|  vCPU  |     1      |     2      |
|   内存   |    1 GB    |    4 GB    |
|  PHP   |    7.2     |    ----    |
| Swoole |   1.9.21   |    ----    |

基准测试在默认的 **Index** 控制器输出 'Hello World' 

```php
<?php

namespace App\HttpController;

use EasySwoole\Core\Http\AbstractInterface\Controller;

class Index extends Controller
{
    function index()
    {
        $this->response()->write('Hello World');
    }
}
```

执行 ab 测试，其中 **172.18.95.34** 为测试机器的内网IP，50万次请求测试结果如下

```bash
ab -c 100 -n 500000 http://172.18.95.34:9501/

This is ApacheBench, Version 2.3 <$Revision: 1807734 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking 172.18.95.34 (be patient)
Completed 50000 requests
Completed 100000 requests
Completed 150000 requests
Completed 200000 requests
Completed 250000 requests
Completed 300000 requests
Completed 350000 requests
Completed 400000 requests
Completed 450000 requests
Completed 500000 requests
Finished 500000 requests


Server Software:        swoole-http-server
Server Hostname:        172.18.95.34
Server Port:            9501

Document Path:          /
Document Length:        63 bytes

Concurrency Level:      100
Time taken for tests:   41.405 seconds
Complete requests:      500000
Failed requests:        0
Non-2xx responses:      500000
Total transferred:      119000000 bytes
HTML transferred:       31500000 bytes
Requests per second:    12075.71 [#/sec] (mean)
Time per request:       8.281 [ms] (mean)
Time per request:       0.083 [ms] (mean, across all concurrent requests)
Transfer rate:          2806.66 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    1   0.5      1       4
Processing:     2    7   2.4      7      66
Waiting:        1    6   2.4      6      66
Total:          3    8   2.4      8      67

Percentage of the requests served within a certain time (ms)
  50%      8
  66%      9
  75%      9
  80%      9
  90%     10
  95%     10
  98%     11
  99%     12
 100%     67 (longest request)
```

## 其他

- [项目官网主页](https://www.easyswoole.com)

- [项目文档仓库](https://github.com/easy-swoole/doc)

- [HTTP基础DEMO](https://github.com/easy-swoole/demo)

- 官方QQ交流群 : **633921431**

- [捐赠](https://www.easyswoole.com/Manual/2.x/Cn/_book/donate.html)
    您的捐赠是对Swoole项目开发组最大的鼓励和支持。我们会坚持开发维护下去。 您的捐赠将被用于:
        
  - 持续和深入地开发
  - 文档和社区的建设和维护

- **easySwoole** 的文档采用 **GitBook** 作为文档撰写工具，若您在使用过程中，发现文档有需要纠正 / 补充的地方，请 **fork** 项目的文档仓库，进行修改补充，提交 **Pull Request** 并联系我们