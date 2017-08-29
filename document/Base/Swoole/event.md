# 事件回调
注册事件回调函数，与swoole_server->on相同。

```
 $server->on('eventName','function for call back')
```
的方式来实现回调事件的注册。

## request 事件
```
$http_server->on('request', function(swoole_http_request $request, swoole_http_response $response) {
     $response->end("<h1>hello swoole</h1>");
})
```
在收到一个完整的Http请求后，会回调此函数。回调函数共有2个参数：

   - $request，Http请求信息对象，包含了header/get/post/cookie等相关信息
   - $response，Http响应对象，支持cookie/header/status等Http操作
> 在onRequest回调函数返回时底层会销毁$request和$response对象，如果未执行$response->end()操作，底层会自动执行一次$response->end("")

## 请求与响应对象
### swoole_http_request 
 - swoole_http_request->$header 
 
    Http请求的头部信息。类型为数组，所有key均为小写。
    ```
    echo $request->header['host'];
    echo $request->header['accept-language'];
    ```
 - swoole_http_request->$server 
 
    Http请求相关的服务器信息，相当于PHP的$_SERVER数组。包含了Http请求的方法，URL路径，客户端IP等信息。数组的key全部为小写，并且与PHP的$_SERVER数组保持一致
    ```
    echo $request->server['request_time'];
    ```
 - swoole_http_request->$get 
 
    Http请求的GET参数，相当于PHP中的$_GET，格式为数组。
    ```
    // 如：index.php?hello=123
    echo $request->get['hello'];
    // 获取所有GET参数
    var_dump($request->get);
    ```
    > 为防止HASH攻击，GET参数最大不允许超过128个
  
 - swoole_http_request->$post 
 
    HTTP POST参数，格式为数组。
    ```
    echo $request->post['hello'];
    ```
    > POST与Header加起来的尺寸不得超过package_max_length的设置，否则会认为是恶意请求,且POST参数的个数最大不超过128个
    
 - swoole_http_request->$cookie 
 
    HTTP请求携带的COOKIE信息，与PHP的$_COOKIE相同，格式为数组。
    ```
    echo $request->cookie['username'];
    ```
    
 - swoole_http_request->$files 
 
    文件上传信息。类型为以form名称为key的二维数组。与PHP的$_FILES相同。
    ```
    Array
    (
        [name] => facepalm.jpg
        [type] => image/jpeg
        [tmp_name] => /tmp/swoole.upfile.n3FmFr
        [error] => 0
        [size] => 15476
    )
    ```
    
    - name 浏览器上传时传入的文件名称
    - type MIME类型
    - tmp_name 上传的临时文件，文件名以/tmp/swoole.upfile开头
    size 文件尺寸
 - swoole_http_request->rawContent 
    
   获取原始的POST包体，用于非application/x-www-form-urlencoded格式的Http POST请求。
   ```
   string swoole_http_request->rawContent();
   ``` 
   - 返回原始POST数据，此函数等同于PHP的fopen('php://input')
   - 标准POST格式，无法调用此函数
   
    
### swoole_http_response 
 - swoole_http_response->header 
   
   设置HTTP响应的Header信息。
   ```
   swoole_http_response->header(string $key, string $value);
   ```
   - $key，Http头的Key
   - $value，Http头的Value
   示例：
   ```
   $responser->header('Content-Type', 'image/jpeg');
   ``` 
    
    >header设置必须在end方法之前</br>
    $key必须完全符合Http的约定，每个单词首字母大写，不得包含中文，下划线或者其他特殊字符</br>
    $value必须填写
 - swoole_http_response->cookie 
   
   设置HTTP响应的cookie信息。此方法参数与PHP的setcookie完全一致。
   ```
   swoole_http_response->cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false);
   ```
   > cookie设置必须在end方法之前
    
 - swoole_http_response->status 
  
   发送Http状态码。
   ```
   swoole_http_response->status(int $http_status_code);
   ```
   - $http_status_code必须为合法的HttpCode，如200， 502， 301, 404等，否则会报错
   - 必须在$response->end之前执行status
  
 - swoole_http_response->gzip 

   启用Http GZIP压缩。压缩可以减小HTML内容的尺寸，有效节省网络带宽，提高响应时间。必须在write/end发送内容之前执行gzip，否则会抛出错误。
   ```
   swoole_http_response->gzip(int $level = 1);
   ```
  
   - $level 压缩等级，范围是1-9，等级越高压缩后的尺寸越小，但CPU消耗更多。默认为1
   - 调用gzip方法后，底层会自动添加Http编码头，PHP代码中不应当再行设置相关Http头
   > jpg/png/gif格式的图片已经经过压缩，无需再次压缩<br/>
  gzip功能依赖zlib库，在编译swoole时底层会检测系统是否存在zlib，如果不存在，gzip方法将不可用
  
 - swoole_http_response->write 
 
   启用Http Chunk分段向浏览器发送相应内容。关于Http Chunk可以参考Http协议标准文档。
   ```
   bool swoole_http_response->write(string $data);
   ```
   - $data要发送的数据内容，最大长度不得超过2M
   - 使用write分段发送数据后，end方法将不接受任何参数
   - 调用end方法后会发送一个长度为0的Chunk表示数据传输完毕
   
 - swoole_http_response->sendfile   
   
   发送文件给客户端。
   ```
   function swoole_http_response->sendfile(string $filename, int $offset = 0, int $length = 0);
   ```
   
   - $filename 要发送的文件名称，文件不存在或没有访问权限sendfile会失败
   - 底层无法推断要发送文件的MIME格式因此需要应用代码指定Content-Type
   - 调用sendfile前不得使用write方法发送Http-Chunk
   - 调用sendfile后底层会自动执行end
   - sendfile不支持gzip压缩
   - $offset 上传文件的偏移量，可以指定从文件的中间部分开始传输数据。此特性可用于支持断点续传。
   - $length 发送数据的尺寸，默认为整个文件的尺寸
   > $length、$offset参数在1.9.11或更高版本可用
   
   例子：
   ```
   $response->header('Content-Type', 'image/jpeg');
   $response->sendfile(__DIR__.$request->server['request_uri']);
   ```
 - swoole_http_response->end 
    
   发送Http响应体，并结束请求处理。
   ```
   swoole_http_response->end(string $html);
   ```
   
   - end操作后将向客户端浏览器发送HTML内容
   - end只能调用一次，如果需要分多次向客户端发送数据，请使用write方法
   - 客户端开启了KeepAlive，连接将会保持，服务器会等待下一次请求
   - 客户端未开启KeepAlive，服务器将会切断连接

  
  
