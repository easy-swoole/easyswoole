# 编程须知
## 注意事项
- 不要在代码中执行sleep以及其他睡眠函数，这样会导致整个进程阻塞
    exit/die是危险的，会导致worker进程退出
- 可通过register_shutdown_function来捕获致命错误，在进程异常退出时做一些请求工作。
- PHP代码中如果有异常抛出，必须在回调函数中进行try/catch捕获异常，否则会导致工作进程退出
- swoole不支持set_exception_handler，必须使用try/catch方式处理异常
- Worker进程不得共用同一个Redis或MySQL等网络服务客户端，Redis/MySQL创建连接的相关代码可以放到onWorkerStart回调函数中。

类/函数重复定义

- 新手非常容易犯这个错误，由于easyPHP-Swoole是常驻内存的，所以加载类/函数定义的文件后不会释放。因此引入类/函数的php文件时必须要使用include_once或require_once，否会发生cannot redeclare function/class 的致命错误。

## 内存管理

- PHP守护进程与普通Web程序的变量生命周期、内存管理方式完全不同,后面会有详细说明，编写常驻进程时需要特别注意。

## 进程隔离

进程隔离也是很多新手经常遇到的问题。修改了全局变量的值，为什么不生效，原因就是全局变量在不同的进程，内存空间是隔离的，所以无效。
所以使用easyPHP-Swoole开发Server程序需要了解进程隔离问题。

不同的进程中PHP变量不是共享，即使是全局变量，在A进程内修改了它的值，在B进程内是无效的
    
  如果需要在不同的Worker进程内共享数据，可以用Redis、MySQL、文件、Swoole\Table、APCu、shmget等工具实现
    不同进程的文件句柄是隔离的，所以在A进程创建的Socket连接或打开的文件，在B进程内是无效，即使是将它的fd发送到B进程也是不可用的

进程克隆。在Server启动时，主进程会克隆当前进程状态，此后开始进程内数据相互独立，互不影响。有疑问的新手可以先弄懂php的pcntl


## 约定规范

- 项目中类名称与类文件(文件夹)命名，均为大驼峰，变量与类方法为小驼峰。Core目录为框架核心目录，Conf目录为系统配置目录，默认应用目录为App。

- 若用easySwoole写HTTP API(网页)服务，控制器搜索路径(名称空间前缀)为"APPLICATION_DIR/Controller"。

- easySWoole中类文件全部为自动加载（PSR-4），支持动态名称空间加载与文件引入。若需添加第三方包(项目)，可以在Conf/Event中的frameInitialize方法获取AutoLoader实例引入,或在对应的业务逻辑代码中引入。

- 在HTTP响应中，于业务逻辑代码中echo $var 并不会将$var内容输出至相应内容中，请调用Response实例中的wirte()方法实现。

