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

#### Doc [For English](https://www.easyswoole.com/Manual/3.x/En/_book/) Click Here

## EasySwoole

EasySwoole 是一款基于Swoole Server 开发的常驻内存型的分布式PHP框架，专为API而生，摆脱传统PHP运行模式在进程唤起和文件加载上带来的性能损失。EasySwoole 高度封装了 Swoole Server 而依旧维持 Swoole Server 原有特性，支持同时混合监听HTTP、自定义TCP、UDP协议，让开发者以最低的学习成本和精力编写出多进程，可异步，高可用的应用服务

## 特性

- 强大的 TCP/UDP Server 框架，多线程，EventLoop，事件驱动，异步，Worker进程组，Task异步任务，毫秒定时器，SSL/TLS隧道加密
- EventLoop API，让用户可以直接操作底层的事件循环，将socket，stream，管道等Linux文件加入到事件循环中
- 定时器、协程对象池、HTTP\SOCK控制器、分布式微服务、RPC支持

## 入门成本

相比传统的FPM框架来说，EasySwoole是有一点的入门成本的，许多设计理念及和环境均与传统的FPM不同，
对于长时间使用LAMP（LANP）技术的开发人员来说会有一段时间的适应期，而在众多的Swoole框架中，EasySwoole上手还是比较容易，根据简单的例子和文档几乎立即就能开启EasySwoole的探索之旅。

## 优势

- 简单易用开发效率高
- 并发百万TCP连接
- TCP/UDP/UnixSock
- 支持异步/同步/协程
- 支持多进程/多线程
- CPU亲和性/守护进程

## 常用功能与组件

- HTTP控制器与自定义路由
- TCP、UDP、WEB_SOCKET控制器
- 多种混合协议通讯
- 异步客户端与协程对象池
- 异步进程、自定义进程、定时器
- 集群分布式支持，例如集群节点通讯，服务发现，RPC
- 全开放系统事件注册器与EventHook
- Redis\Kafka\RabbitMQ等队列消费与订阅
- Crontab定时规则支持

## 基准测试

配置1: 阿里云 1H1G 无任何优化的实例 安装CentOS-7.5/64位 PHP-7.2.11 Swoole-4.2.8 EasySwoole-3.0.7

配置2: 阿里云 8H16G 无任何优化的实例 安装CentOS-7.5/64位 PHP-7.2.11 Swoole-4.2.8 EasySwoole-3.0.7

另一台机器运行ab工具对以上两台实例进行压测，三台机器均在同一个内网下

```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;


/**
 * Class Index
 * @package App\HttpController
 */
class Index extends Controller
{
    function index()
    {
        $this->response()->write('Hello World');
    }
}
```

### 阿里云1核1G机器ab压测结果

> 执行压测命令 : ab -c 100 -n 10000 http://192.168.0.11:9501/

```
Server Software:        EasySwoole
Server Hostname:        192.168.0.11
Server Port:            9501

Document Path:          /
Document Length:        21 bytes

Concurrency Level:      100
Time taken for tests:   0.652 seconds
Complete requests:      10000
Failed requests:        0
Write errors:           0
Total transferred:      1690000 bytes
HTML transferred:       210000 bytes
Requests per second:    15325.16 [#/sec] (mean)
Time per request:       9.685 [ms] (mean)
Time per request:       0.097 [ms] (mean, across all concurrent requests)
Transfer rate:          2592.05 [Kbytes/sec] received
```

### 阿里云8核16G机器ab压测结果

> 执行压测命令 : ab -c 100 -n 10000 http://192.168.0.4:9501/

```
Server Software:        EasySwoole
Server Hostname:        192.168.0.4
Server Port:            9501

Document Path:          /
Document Length:        21 bytes

Concurrency Level:      100
Time taken for tests:   0.746 seconds
Complete requests:      10000
Failed requests:        0
Write errors:           0
Total transferred:      1690000 bytes
HTML transferred:       210000 bytes
Requests per second:    66935.97 [#/sec] (mean)
Time per request:       1.149 [ms] (mean)
Time per request:       0.015 [ms] (mean, across all concurrent requests)
Transfer rate:          2265.40 [Kbytes/sec] received
```

## 其他

- [项目官网主页](https://www.easyswoole.com)
- [码云](https://gitee.com/easyswoole/easyswoole)
- [项目文档仓库](https://github.com/easy-swoole/doc)
- [HTTP基础DEMO](https://github.com/easy-swoole/demo)

- 官方QQ交流群 : **633921431**

- [捐赠](https://www.easyswoole.com/Manual/2.x/Cn/_book/donate.html)
    您的捐赠是对Swoole项目开发组最大的鼓励和支持。我们会坚持开发维护下去。 您的捐赠将被用于:
        
  - 持续和深入地开发
  - 文档和社区的建设和维护

- **easySwoole** 的文档采用 **GitBook** 作为文档撰写工具，若您在使用过程中，发现文档有需要纠正 / 补充的地方，请 **fork** 项目的文档仓库，进行修改补充，提交 **Pull Request** 并联系我们
