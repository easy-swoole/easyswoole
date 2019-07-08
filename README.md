English  |  [中文](./README.zh-CN.md)  
![](easyswoole.png)

[![Latest Stable Version](https://poser.pugx.org/easyswoole/easyswoole/v/stable)](https://packagist.org/packages/easyswoole/easyswoole)
[![Total Downloads](https://poser.pugx.org/easyswoole/easyswoole/downloads)](https://packagist.org/packages/easyswoole/easyswoole)
[![Latest Unstable Version](https://poser.pugx.org/easyswoole/easyswoole/v/unstable)](https://packagist.org/packages/easyswoole/easyswoole)
[![License](https://poser.pugx.org/easyswoole/easyswoole/license)](https://packagist.org/packages/easyswoole/easyswoole)
[![Monthly Downloads](https://poser.pugx.org/easyswoole/easyswoole/d/monthly)](https://packagist.org/packages/easyswoole/easyswoole)


## EasySwoole

EasySwoole is a distributed, persistent memory PHP framework based on the Swoole extension. It was created specifically for APIs to get rid of the performance penalties associated with process calls and file loading. EasySwoole highly encapsulates the Swoole Server and still maintains the original features of the Swoole server, supports simultaneous monitoring of HTTP, custom TCP, and UDP protocols, allowing developers to write multi-process, asynchronous, and highly available applications with minimal learning cost and effort.
          
- Base on Swoole extension
- Built-in HTTP, TCP, WebSocket,Udp Coroutine Server
- Global dependency injection container
- PSR-7 based HTTP message implementation
- HTTP,TCP, WebSocket, Udp middleware support
- Scalable high performance RPC
- Database ORM
- Mysql, Redis, RPC, HTTP Coroutine Clients
- Coroutine and asynchronous task delivery
- Custom user processes
- RESTful supported
- High performance router
- Fast and flexible parameter validator
- Powerful log component
- Universal connection pools
- Remote Console support
- Crontab Rule Timer support

## ab Test

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

## Quick Start
```
composer require easyswoole/easyswoole=3.x
php vendor/bin/easyswoole install
php easyswoole start
```

## DockerFile
```
FROM php:7.3

# Version
ENV PHPREDIS_VERSION 4.0.1
ENV SWOOLE_VERSION 4.4.0
ENV EASYSWOOLE_VERSION 3.x-dev

# Timezone
RUN /bin/cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime \
    && echo 'Asia/Shanghai' > /etc/timezone

# Libs
RUN apt-get update \
    && apt-get install -y \
    curl \
    wget \
    git \
    zip \
    libz-dev \
    libssl-dev \
    libnghttp2-dev \
    libpcre3-dev \
    && apt-get clean \
    && apt-get autoremove

# Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer self-update --clean-backups

# PDO extension
RUN docker-php-ext-install pdo_mysql

# Bcmath extension
RUN docker-php-ext-install bcmath

# Redis extension
RUN wget http://pecl.php.net/get/redis-${PHPREDIS_VERSION}.tgz -O /tmp/redis.tar.tgz \
    && pecl install /tmp/redis.tar.tgz \
    && rm -rf /tmp/redis.tar.tgz \
    && docker-php-ext-enable redis

# Swoole extension
RUN wget https://github.com/swoole/swoole-src/archive/v${SWOOLE_VERSION}.tar.gz -O swoole.tar.gz \
    && mkdir -p swoole \
    && tar -xf swoole.tar.gz -C swoole --strip-components=1 \
    && rm swoole.tar.gz \
    && ( \
    cd swoole \
    && phpize \
    && ./configure --enable-async-redis --enable-mysqlnd --enable-openssl --enable-http2 \
    && make -j$(nproc) \
    && make install \
    ) \
    && rm -r swoole \
    && docker-php-ext-enable swoole

WORKDIR /var/www/code

# Install easyswoole
RUN cd /var/www/code \
    && composer require easyswoole/easyswoole=${EASYSWOOLE_VERSION} \
    && php vendor/bin/easyswoole install

EXPOSE 9501

ENTRYPOINT ["php", "/var/www/code/easyswoole", "start"]
```

## Others 
- [Home Page](https://www.easyswoole.com)
- [Git For Doc](https://github.com/easy-swoole/doc)
- [Git For Demo](https://github.com/easy-swoole/demo)
- QQ交流群
    - VIP群 579434607 （本群需要付费599元）
    - EasySwoole官方一群 633921431(已满)
    - EasySwoole官方二群 709134628
    
- 商业支持：
    - QQ 291323003
    - EMAIL admin@fosuss.com
