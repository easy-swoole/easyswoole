# 异步进程
  在Swoole Server中，通过swoole_server->task可以投递一个异步任务到task_worker池中。此函数是非阻塞的，执行完毕会立即返回。Task Worker进程可以继续处理新的请求。使用Task功能，必须先设置 task_worker_num，并且必须设置Server的onTask和onFinish事件回调函数。
  ```
  int swoole_server::task(mixed $data, int $dst_worker_id = -1) 
  $task_id = $serv->task("some data");
  //swoole-1.8.6或更高版本
  $serv->task("taskcallback", -1, function (swoole_server $serv, $task_id, $data) {
      echo "Task Callback: ";
      var_dump($task_id, $data);
  });
  ```
  
  - $data要投递的任务数据，可以为除资源类型之外的任意PHP变量
  - $dst_worker_id可以制定要给投递给哪个task进程，传入ID即可，范围是0 - (serv->task_worker_num -1)
  - 调用成功，返回值为整数$task_id，表示此任务的ID。如果有finish回应，onFinish回调中会携带$task_id参数
  - 调用失败，返回值为false
  - 未指定目标Task进程，调用task方法会判断Task进程的忙闲状态，底层只会向处于空闲状态的Task进程投递任务。如果所有Task进程均处于忙的状态，底层会轮询投递任务到各个进程。可以使用 server->stats 方法获取当前正在排队的任务数量。
  - 1.8.6版本增加了第三个参数，可以直接设置onFinish函数，如果任务设置了回调函数，Task返回结果时会直接执行指定的回调函数，不再执行Server的onFinish回调
 > $dst_worker_id在1.6.11+后可用，默认为随机投递</br>
   $task_id是从0-42亿的整数，在当前进程内是唯一的</br>
   task方法不能在task进程/用户自定义进程中调用
   
此功能用于将慢速的任务异步地去执行，比如一个聊天室服务器，可以用它来进行发送广播。当任务完成时，在task进程中调用$serv->finish("finish")告诉worker进程此任务已完成。当然swoole_server->finish是可选的。
 
task底层使用Unix Socket管道通信，是全内存的，没有IO消耗。单进程读写性能可达100万/s，不同的进程使用不同的管道通信，可以最大化利用多核。
> AsyncTask功能在1.6.4版本增加，默认不启动task功能，需要在手工设置task_worker_num来启动此功能</br>
task_worker的数量在swoole_server::set参数中调整，如task_worker_num => 64，表示启动64个进程来接收异步任务
   
## 配置参数
swoole_server->task/taskwait/finish 3个方法当传入的$data数据超过8K时会启用临时文件来保存。当临时文件内容超过 server->package_max_length 时底层会抛出一个警告。此警告不影响数据的投递，过大的Task可能会存在性能问题
```
WARN: task package is too big.
```
> server->package_max_length 默认为2M
## 注意事项
- 使用task必须为Server设置onTask和onFinish回调，否则swoole_server->start会失败
- task操作的次数必须小于onTask处理速度，如果投递容量超过处理能力，task会塞满缓存区，导致worker进程发生阻塞。worker进程将无法接收新的请求
- 使用addProcess添加的用户进程中无法使用task投递任务，请使用sendMessage接口与Task工作进程通信

### 使用例子
请见easySwoole中异步进程的用例。
 
 https://github.com/kiss291323003/easyswoole/blob/master/example/multiUsage_01/App/Controller/Test/Async.php

