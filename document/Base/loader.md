# 自动加载
easySwoole支持标准的PSR-4自动加载。
## 添加名称空间
```
$loader = AutoLoader::getInstance();
$loader->addNamespace('new name space',"dir path");
```
> 如果不懂如何使用，可以参考Core.php中的registerAutoLoader方法，里面的FastRoute、SuperClosure、PhpParser均为第三方组件。

## 引入当个文件
```
$loader->requireFile('file path');
```

> 当成功引入时，返回true,若引入失败则返回false。该函数实际上是对require_once的封装。