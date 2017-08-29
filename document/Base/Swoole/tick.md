# 定时器
通过调用swoole_server->tick()可以新增一个定时器。
> worker进程结束运行后，所有定时器都会自动销毁</br>
  tick/after定时器不能在swoole_server->start之前使用
  
## 在request事件中使用
```
   function onRequest()use($server) {
       $server->tick(1000, function() use ($server, $fd) {
           echo "hello world";
       });
   }
```
## 在onWorkerStart中使用
   
 - 低于1.8.0版本task进程不能使用tick/after定时器，所以需要使用$serv->taskworker进行判断
 - task进程可以使用addtimer间隔定时器
```
function onWorkerStart(swoole_server $serv, $worker_id)
{
    if (!$serv->taskworker) {
        $serv->tick(1000, function ($id) {
            var_dump($id);
        });
    }
    else
    {
        $serv->addtimer(1000);
    }
}
```
