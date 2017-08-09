# easySwoole
easySwoole 专为API而生，是一款常驻内存化的PHP开发框架，摆脱传统PHP运行模式在进程唤起和文件加载上带来的性能损失，自带服务器功能，无需依赖Apache或Nginx运行。在web服务器模式下，支持多层级(组模式)控制器访问与多种事件回调,高度封装了Swoole Server 而依旧维持Swoole Server原有特性，支持在 Server 中监听自定义的TCP、UDP协议，让开发者可以最低的学习成本和精力，编写出多进程，可定时，可异步，高可用的应用服务。

### 本项目基于easyPHP与Swoole拓展实现

##### [easyPHP](https://github.com/kiss291323003/easyPHP): https://github.com/kiss291323003/easyPHP
##### [Swoole](http://www.swoole.com/): http://www.swoole.com/


## 主要特性:

#### 维持了Swoole Server中的全部特性：

 - 强大的TCP/UDP Server框架，多线程，EventLoop，事件驱动，异步，Worker进程组，Task异步任务，毫秒定时器，SSL/TLS隧道加密。
 - EventLoop API，让用户可以直接操作底层的事件循环，将socket，stream，管道等Linux文件加入到事件循环中。
#### 维持了easyPHP中的全部特性
 - 高度全局化请求对象与响应对象封装，方便二次开发。
 - 支持快速路由,请求拦截,多种事件回调，容器托管服务。   
 
#### 优势:

   - 简单易用开发效率高
   - 并发百万TCP连接
   - TCP/UDP/UnixSock
   - 支持异步/同步/协程
   - 支持多进程/多线程
   - CPU亲和性/守护进程

# 项目文档

项目文档已经移至[https://kiss291323003.gitbooks.io/easyphp-swoole/content/](https://kiss291323003.gitbooks.io/easyphp-swoole/content/)

QQ交流群 ： 633921431
 
# bug反馈

 如果您在使用过程中有任何的疑问，请联系作者 admin@robindata.cn

# 相关资源
## docker测试镜像
  如果您想快速体验easyswoole的功能，请直接下载测试镜像。
  https://dev.aliyun.com/detail.html?spm=5176.1972343.2.4.dtD683&repoId=62694（仅供测试，请勿用于生产环境）