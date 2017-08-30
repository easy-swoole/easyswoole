# easySwoole

easySwoole 专为API而生，是一款常驻内存化的PHP开发框架，摆脱传统PHP运行模式在进程唤起和文件加载上带来的性能损失，自带服务器功能，无需依赖Apache或Nginx运行。在web服务器模式下，支持多层级(组模式)控制器访问与多种事件回调,高度封装了Swoole Server 而依旧维持Swoole Server 原有特性，支持在 Server 中监听自定义的TCP、UDP协议，让开发者可以最低的学习成本和精力，编写出多进程，可定时，可异步，高可用的应用服务。
本项目基于easyPHP与Swoole拓展实现：

- easyPHP https://github.com/kiss291323003/easyPHP
   
- Swoole http://www.swoole.com/

## 特性:

### 维持了 Swoole Server 中的全部特性：
- 强大的 TCP/UDP Server 框架，多线程，EventLoop，事件驱动，异步，Worker进程组，Task异步任务，毫秒定时器，SSL/TLS隧道加密
- EventLoop API，让用户可以直接操作底层的事件循环，将socket，stream，管道等Linux文件加入到事件循环中。

### 维持了easyPHP中的全部特性：
- 高度全局化请求对象与响应对象封装，方便二次开发。
- 支持快速路由,请求拦截,多种事件回调，容器托管服务。

## 优势:

- 简单易用开发效率高
- 并发百万TCP连接
- TCP/UDP/UnixSock
- 支持异步/同步/协程
- 支持多进程/多线程
- CPU亲和性/守护进程

## 关于ab基准测试:

    系统: CentOS 7.1 桌面版
    CPU: i5 6500
    内存: 8G
    php: 5.6.30
    Swoole: 1.8.13-stable
    测试代码: Index控制器中输出"hello world"并发送header "X-Server"=>""easyPHP"
    ab -c 500 -n 500000 http://127.0.0.1:9501/ 测试结果如下

    Server Software:        easyPHP
    Server Hostname:        127.0.0.1
    
    Server Port:            9501
    Document Path:          /
    Document Length:        20 bytes
    
    Concurrency Level:      500
    Time taken for tests:   30.268 seconds
    Complete requests:      500000
    Failed requests:        0
    Write errors:           0
    Total transferred:      97500000 bytes
    HTML transferred:       10000000 bytes
    Requests per second:    16519.16 [#/sec] (mean)
    Time per request:       30.268 [ms] (mean)
    Time per request:       0.061 [ms] (mean, across all concurrent requests)
    Transfer rate:          3145.74 [Kbytes/sec] received
    
    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0   15   1.0     15      25
    Processing:     2   15   1.3     15      37
    Waiting:        1   12   2.0     12      31
    Total:         17   30   1.2     30      52
    
    Percentage of the requests served within a certain time (ms)
      50%     30
      66%     30
      75%     31
      80%     31
      90%     31
      95%     31
      98%     33
      99%     34
     100%     52 (longest request)
     
## VS LAMP

使用 easySwoole 和传统的 LAMP 模式最大不同的地方在于整个 easySwoole 是常驻内存的，所以 PHP 守护进程和普通的 web 程序在变量的生命周期，内存管理方式等方面完全不同。针对一些萌新的 PHPer，给出以下的避坑指南：
- easySwoole 支持多进程，不同进程中的 PHP 变量是不共享。即使是 $_GET, $_POST 等全局变量，在 A 进程中被修改，并不会影响到 B 进程
- easySwoole 是常驻内存型的应用，所以不要在代码中编写 die, exit 等代码，这些代码会导致进程退出。这也是 easySwoole 不直接支持 composer 的原因，很多 composer 包中使用了很多 exit 代码，如果贸然使用可能会导致进程意外退出。
- 不要在代码中调用 sleep 等睡眠函数，除非你明确知道其作用和效果。使用 sleep 等函数会导致进程阻塞。
- 使用 require_once, include_once 代替 require, include. 由于 easySwoole 是常驻内存，所以加载 php 文件之后不会释放，因此一定要使用 *_once 来避免多次加载同一个文件，否则会发生 cannot redeclare function/class 的错误。
- PHP 代码中如果有异常抛出，必须在回调函数中进行 try/catch 捕获异常，否则会导致工作进程退出。
- 在Http应用中，使用 echo, var_dump 函数进行输出并不会被响应至客户端，请使用框架中的 $this->response()->write() 代替。

## [相关文档](http://117.25.148.21:9501/)

## QQ交流群 ： 633921431
 
## bug反馈

 如果您在使用过程中有任何的疑问，请联系作者 admin@robindata.cn

## 相关资源
## docker测试镜像
  如果您想快速体验easyswoole的功能，请直接下载测试镜像。
  [https://dev.aliyun.com/detail.html?spm=5176.1972343.2.4.dtD683&repoId=62694](https://dev.aliyun.com/detail.html?spm=5176.1972343.2.4.dtD683&repoId=62694)（仅供测试，请勿用于生产环境）
  
  docker pull registry.cn-qingdao.aliyuncs.com/foreign/easyswoole