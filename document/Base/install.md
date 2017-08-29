# 安装与启动
## 框架安装
easySwoole 项目依赖于 Swoole 扩展，在使用 easySwoole 之前需要先安装 swoole 扩展。

从 [easyswoole](https://github.com/kiss291323003/easyswoole) 下载源码，下载下来之后目录结构如下:
```
├── example  ----------------示例代码目录
├── src      ----------------框架所在目录
├── ide-helper     ----------IDE代码补全提示
└── .htaccess-apache --------Apache 反向代理规则
```
其中，src 目录中的内容为项目需要的,目录结构如下 :
```
├── App   -------------------应用目录
├── Conf  -------------------配置与事件配置目录
├── Core  -------------------框架核心目录
├── server.php  -------------服务管理脚本
└── unitTest.php   ----------单元测试脚本
```
## Hello World
进入 src 目录，执行
```
php server.php start 
```
启动 easySwoole。在浏览器输入 ip:9501/ 可以看到欢迎使用语说明安装成功。

## 服务启动
easySwoole 不依赖 Apache/Nginx, 自带 HttpServer 功能，进入项目根目录，执行 php server.php start 就可以启动 easySwoole。easySwoole 只有三个命令参数 ： start(启动), stop(停止), reload(重载)

在启动 easySwoole 的时候也可以指定一些配置参数，通过执行 php server.php --start help 可以查看所有参数和具体的参数含义。
```
➜  swoole php server.php --start help
执行php server.php start 即可启动服务。启动可选参数为:
--daemonize-boolean       是否以系统守护模式运行
--port-portNumber         指定服务监听端口
--pidFile-fileName        指定服务PID存储文件
--SwooleLog-fileName      指定Swoole日志文件
--workerNum-num           设置worker进程数
--taskWorkerNum-num       设置Task进程数
--user-userName           指定以某个用户身份执行
--group-groupName         指定以某个用户组身份执行
--taskWorkerNum-num       设置Task进程数
--cpuAffinity-boolean     是否开启CPU亲和
```
> 这里注意一点，easySwoole 属于常驻内存的应用，当修改代码之后要重启 easySwoole 代码才能生效。