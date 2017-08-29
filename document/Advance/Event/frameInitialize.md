# 框架初始化事件

在执行该事件时，框架已经完成的工作有：
- 系统常量ROOT的定义
- 注册自动加载与常用名称空间
- 定义错误处理函数

在该回调函数内可以创建一些全局配置。例如：
```
date_default_timezone_set('Asia/Shanghai');
```
或者是引入第三方文件与名称空间：
```
$loader = AutoLoader::getInstance();
$loader->requireFile("App/Vendor/Aip/Speech/AipSpeech.php");
$loader->addNamespace("OSS","App/Vendor/Ali/OSS");
```