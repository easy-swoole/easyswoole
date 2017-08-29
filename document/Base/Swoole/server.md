# Swoole Http Server
swoole-1.7.7起增加了内置Http服务器的支持,swoole_http_server 继承自swoole_server，是一个完整的http服务器实现，通过几行代码即可写出一个异步非阻塞多进程的Http服务器。
```
$http = new swoole_http_server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});
$http->start();
```
> swoole_http_server对Http协议的支持并不完整，建议仅作为应用服务器。并且在前端增加Nginx或者Apache作为代理,仅将API请求转发给Swoole Server处理。

### Nginx转发规则
```
server {
    root /data/wwwroot/;
    server_name local.swoole.com;
    location / {
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header X-Real-IP $remote_addr;
        if (!-e $request_filename) {
             proxy_pass http://127.0.0.1:9501;
        }
    }
}
```
### Apache转发规则
```
<IfModule mod_rewrite.c>
  Options +FollowSymlinks
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  #RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]  fcgi下无效
  RewriteRule ^(.*)$  http://127.0.0.1:9501/$1 [QSA,P,L]
   #请开启 proxy_mod proxy_http_mod requset_mod
</IfModule>
```