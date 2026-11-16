
![](easyswoole.png)

[![最新稳定版本](https://poser.pugx.org/easyswoole/easyswoole/v/stable)](https://packagist.org/packages/easyswoole/easyswoole)
[![总下载量](https://poser.pugx.org/easyswoole/easyswoole/downloads)](https://packagist.org/packages/easyswoole/easyswoole)
[![最新开发版本](https://poser.pugx.org/easyswoole/easyswoole/v/unstable)](https://packagist.org/packages/easyswoole/easyswoole)
[![许可证](https://poser.pugx.org/easyswoole/easyswoole/license)](https://packagist.org/packages/easyswoole/easyswoole)
[![月下载量](https://poser.pugx.org/easyswoole/easyswoole/d/monthly)](https://packagist.org/packages/easyswoole/easyswoole)


# EasySwoole —— 高性能 Swoole 框架

[EasySwoole](http://www.easyswoole.com/) 是基于 Swoole 扩展的分布式、常驻内存 PHP 框架，面向 API 等应用场景，减少进程调用与文件加载带来的性能开销。框架封装了 Swoole Server，并保留其原有能力，支持同时监听 HTTP、自定义 TCP 和 UDP 协议，帮助开发者构建多进程、异步和高可用应用。

- 基于 Swoole 扩展
- 内置 HTTP、TCP、WebSocket 和 UDP 协程服务器
- 全局依赖注入容器
- 基于 PSR-7 的 HTTP 消息实现
- 支持 HTTP、TCP、WebSocket 和 UDP 中间件
- 可扩展的高性能 RPC
- 数据库 ORM
- MySQL、Redis、RPC 和 HTTP 协程客户端
- 协程与异步任务投递
- 自定义用户进程
- 支持 RESTful
- 高性能路由
- 快速、灵活的参数校验器
- 日志组件
- 通用连接池
- 支持远程控制台
- 支持 Crontab 规则定时任务

## 文档

- [官方文档](http://www.easyswoole.com)
- [文档源码](https://github.com/easy-swoole/doc-3.8)

## ab 压力测试

```php
<?php

namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;


/**
 * 示例首页控制器。
 * @package App\HttpController
 */
class Index extends Controller
{
    public function index()
    {
        $this->response()->write('Hello World');
    }
}
```

### 1 核 CPU、1 GB 内存

> 测试命令：ab -c 100 -n 10000 http://192.168.0.11:9501/

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

### 8 核 CPU、16 GB 内存

> 测试命令：ab -c 100 -n 10000 http://192.168.0.4:9501/

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

## 快速开始
```
composer require easyswoole/easyswoole=3.x
php vendor/bin/easyswoole.php install
php easyswoole.php server start
```

安装时会列出生成、更新、保留和跳过的文件。自动化安装可用 `php vendor/bin/easyswoole.php install --with-examples` 生成缺失的 HTTP 示例。终端中仍会询问是否覆盖已有文件，默认保留。


## 其他信息

- QQ交流群
    - VIP群 579434607 （本群需要付费599元）
    - EasySwoole官方一群 633921431(已满)
    - EasySwoole官方二群 709134628(已满)
    - EasySwoole官方三群 932625047(已满)
    - EasySwoole官方四群 779897753(已满)
    - EasySwoole官方五群 853946743(已满)
    - EasySwoole官方六群 524475224

- 商业支持：
    - QQ 291323003
    - 邮箱：admin@fosuss.com
