中文 | [English](./README.md)

![](easyswoole.png)

[![Latest Stable Version](https://poser.pugx.org/easyswoole/easyswoole/v/stable)](https://packagist.org/packages/easyswoole/easyswoole)
[![Total Downloads](https://poser.pugx.org/easyswoole/easyswoole/downloads)](https://packagist.org/packages/easyswoole/easyswoole)
[![Latest Unstable Version](https://poser.pugx.org/easyswoole/easyswoole/v/unstable)](https://packagist.org/packages/easyswoole/easyswoole)
[![License](https://poser.pugx.org/easyswoole/easyswoole/license)](https://packagist.org/packages/easyswoole/easyswoole)
[![Monthly Downloads](https://poser.pugx.org/easyswoole/easyswoole/d/monthly)](https://packagist.org/packages/easyswoole/easyswoole)


# EasySwoole - 一款高性能 Swoole 框架

[EasySwoole](http://www.easyswoole.com/) 是一款基于 Swoole Server 开发的常驻内存型的分布式 PHP 框架，专为 API 而生，摆脱传统 PHP 运行模式在进程唤起和文件加载上带来的性能损失。EasySwoole 高度封装了 Swoole Server 而依旧维持 Swoole Server 原有特性，支持同时混合监听 HTTP、自定义 TCP、UDP 协议，让开发者以最低的学习成本和精力编写出多进程、可异步、高可用的应用服务。
          
- 基于 Swoole 扩展
- 内置HTTP、TCP、WebSocket、UD P协程服务器
- 全局依赖注入容器
- 基于 PSR-7 的 HTTP 消息实现
- HTTP、TCP、WebSocket、UDP 中间件支持
- 可扩展的高性能 RPC
- 数据库 ORM
- MYSQL、Redis、RPC、HTTP 协程客户端
- 协程和异步任务
- 用户自定义进程
- 支持 RESTful
- 高性能路由
- 快速灵活的参数验证器
- 强大的日志组件
- 通用连接池
- 远程控制台支持
- Crontab及定时器支持

## 文档 

- [英文文档](http://www.easyswoole.com)
- [中文文档](http://www.easyswoole.com)
- [文档 Git 仓库](https://github.com/easy-swoole/doc-3.7)

## ab 压力测试

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
    public function index()
    {
        $this->response()->write('Hello World');
    }
}
```

### 1 Core 1G RAM

> command : ab -c 100 -n 10000 http://192.168.0.11:9501/

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

### 8 Core 16G RAM

> command : ab -c 100 -n 10000 http://192.168.0.4:9501/

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

## 快速开始使用
```
composer require easyswoole/easyswoole=3.7.x
php vendor/bin/easyswoole.php install
php easyswoole.php server start
```

## Docker
### 获取 Docker 镜像
```
docker pull easyswoolexuesi2021/easyswoole:php8.1.22-alpine3.16-swoole4.8.13
```
> 更多 Docker 镜像可查看：[Docker Hub](https://hub.docker.com/r/easyswoolexuesi2021/easyswoole) 或 [Dockerfile 仓库](https://github.com/XueSiLf/easyswoole-docker)

### 运行容器

```
docker run --name easyswoole \
-v /workspace/project:/var/www/project \
-p 9501:9501 -it \
--privileged -u root \
--entrypoint /bin/sh \
easyswoolexuesi2021/easyswoole:php8.1.22-alpine3.16-swoole4.8.13
```
- 工作目录: ***/var/www/project***
- 运行 Easyswoole : 
```bash
composer require easyswoole/easyswoole=3.7.x
php vendor/bin/easyswoole.php install
php easyswoole.php server start
```

## 其他 
- [Git For Demo](https://github.com/easy-swoole/demo)
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
    - EMAIL admin@fosuss.com
