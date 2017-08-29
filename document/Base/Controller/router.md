# 路由
easySwoole支持路由拦截。其路由利用[fastRoute](https://github.com/nikic/FastRoute)实现，因此其路由规则与其保持一致，具体请见fastRoute的gitHub主页。
## 使用
若需要再easySwoole使用路由拦截功能，请在应用目录（默认为App）下，建立Router类，井继承Core\AbstractInterface\AbstractRouter实现addRouter方法。
```H
namespace App;

use Core\AbstractInterface\AbstractRouter;
use Core\Component\Logger;
use FastRoute\RouteCollector;
class Router extends AbstractRouter
{

    function addRouter(RouteCollector $routeCollector)
    {
        // TODO: Implement addRouter() method.
        $routeCollector->addRoute(['GET','POST'],"/router",function (){
             $this->response()->write("match router1 now");
             $this->response()->end();
         });
         
         $routeCollector->addRoute(['GET','POST'],"/router2",function (){
             $this->response()->write("match router2 now");
             $this->response()->end();
          });  
    }
}
```
> 注意：若在路由回调函数中不结束该请求响应，则该次请求将会继续进行Dispatch并尝试寻找对应的控制器进行响应处理。

