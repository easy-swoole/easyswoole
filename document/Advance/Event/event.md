# 系统事件
easySwoole预留了多种事件入口，以方便用户更加自由地使用easySwoole框架。
其中除框架预处理，其余的事件入口均在Conf/Event.php下，其中Event类必须继承use Core\AbstractInterface\AbstractEvent。以下为开发者常用事件：

* [frameInitialize](frameInitialize.md)

* [beforeWorkerStart](beforeWorkerStart.md)

* [onWorkerStart](onWorkerStart.md)

* [onRequest](onRequest.md)

* [onResponse](onResponse.md)