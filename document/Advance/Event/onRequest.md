# 请求事件
当easySwoole收到任何的HTTP请求时，均会执行该事件。该事件可以对HTTP请求全局拦截。
```
$sec = new Security();
if($sec->check($request->getRequestParam())){
   $response->write("do not attack");
   $response->end();
   return;
}
if($sec->check($request->getCookieParams())){
   $response->write("do not attack");
   $response->end();
   return;
}
```
或者是
```
$cookie = $request->getCookieParams('who');
//do cookie auth
if(auth fail){
   $response->end();
   return;
}
```
> 若在改事件中，执行 $response->end(),则该次请求不会进入路由匹配阶段。