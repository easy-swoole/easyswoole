## 项目说明:
### 本项目基于easyPHP与Swoole拓展实现

##### [easyPHP](https://github.com/kiss291323003/easyPHP): https://github.com/kiss291323003/easyPHP
##### [Swoole](http://www.swoole.com/): http://www.swoole.com/

easyPHP-Swoole 专为API而生，支持多层级(组模式)控制器访问与多种事件回调,高度封装了Swolle Server 而依旧维持Swoole Server原有特性，支持在 Server 中监听自定义的TCP、UDP协议，让开发者可以最低的学习成本和精力，编写出多进程，可定时，可异步，高可用的应用服务。

####关于ab基准测试：

- 系统: CentOS 7.1 桌面版
- CPU: i5 6500
- 内存: 8G
- php: 5.6.30
- Swoole: 1.8.13-stable
- 测试代码: Index控制器中输出"hello world"并发送header "X-Server"=>""easyPHP"

![](example/abTest/ab.png)

## 主要特性:

####维持了swoole Server中的全部特性：

 - 强大的TCP/UDP Server框架，多线程，EventLoop，事件驱动，异步，Worker进程组，Task异步任务，毫秒定时器，SSL/TLS隧道加密。
 - EventLoop API，让用户可以直接操作底层的事件循环，将socket，stream，管道等Linux文件加入到事件循环中。
####维持了easyPHP中的全部特性
 - 高度全局化请求对象与响应对象封装，方便二次开发。
 - 支持快速路由,请求拦截,多种事件回调，容器托管服务。
## 项目文档
待完善
