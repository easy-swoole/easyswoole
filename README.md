# easySwoole
EasySwoole 是一款基于Swoole Server 开发的常驻内存型PHP框架，专为API而生，摆脱传统PHP运行模式在进程唤起和文件加载上带来的性能损失。EasySwoole 高度封装了Swoole Server 而依旧维持Swoole Server 原有特性，支持同时混合监听HTTP、自定义TCP、UDP协议，让开发者以最低的学习成本和精力编写出多进程，可异步，高可用的应用服务。 

## 特性

- 强大的 TCP/UDP Server 框架，多线程，EventLoop，事件驱动，异步，Worker进程组，Task异步任务，毫秒定时器，SSL/TLS隧道加密
- EventLoop API，让用户可以直接操作底层的事件循环，将socket，stream，管道等Linux文件加入到事件循环中。

## 优势:

- 简单易用开发效率高
- 并发百万TCP连接
- TCP/UDP/UnixSock
- 支持异步/同步/协程
- 支持多进程/多线程
- CPU亲和性/守护进程

## 关于ab基准测试:
```
系统: CentOS 7.1 
CPU: 阿里云单核
内存: 1G
php: 5.6.30
Swoole: 1.9.17
测试代码: Index控制器中输出"hello world"并发送header "X-Server"=>"easySwoole"
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
   100%    52 (longest request)
```
## 其他

- QQ交流群 ： 633921431

- [项目官网与文档](http://www.easyswoole.com/)

- [文档维护地址](https://github.com/kiss291323003/doc-easyswoole) 
    easySwoole采用gitbook作为文档撰写工具，若您在使用过程中，有发现文档需要纠正/补充。请直接提交在github上。
