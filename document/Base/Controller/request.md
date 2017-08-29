# Request对象

## 生命周期
Request对象在系统中以单例模式存在，自收到客户端HTTP请求时自动创建，直至请求结束自动销毁。Request对象完全符合[PSR7](psr-7.md)中的所有规范。
## 方法列表
### getInstance()
用于获取当前请求实例。
```
$request = Request::getInstance()
```
### getRequestParam()
用于获取用户通过POST或者GET提交的参数（注意：若POST与GET存在同键名参数，则以POST为准）。

示例：
```
$data = $request->getRequestParam();
var_dump($data);

$orderId = $request->getRequestParam('orderId');
var_dump($orderId);

$keys = array(
    "orderId","type"
);
$mixData = $request->getRequestParam($keys);
var_dump($mixData);
```
### getSwooleRequest()
该方法用于获取当前的swoole_http_request对象。

## PSR-7规范ServerRequest对象中常用方法
### getCookieParams()
该方法用于获取HTTP请求中的cookie信息
```
$all = $request->getCookieParams();
var_dump($all);
$who = $request->getCookieParams('who');
var_dump($who);
```
### getUploadedFiles()
该方法用于获取客户端上传的全部文件。
```
$data = $request->getUploadFiles();
var_dump($data);
```
> 注意，getUploadedFiles为返回所有的文件全部为Core\Http\Message\UploadFile实例。其中键名为上传文件时的表单名。

### getUploadedFile($name)
该方法用于获取客户端上传的某个文件。若该文件存在时，则返回对应的Core\Http\Message\UploadFile对象
#### 关于 Core\Http\Message\UploadFile对象,详见[PSR7规范](Base/Controller/psr-7.md)
### getBody()
该方法用于获取以非form-data或x-www-form-urlenceded编码格式POST提交的原始数据，相当于PHP中的$HTTP_RAW_POST_DATA。
> 注意,该方法返回的是Core\Http\Message\Stream实例，具体请见[PSR7规范](Base/Controller/psr-7.md)