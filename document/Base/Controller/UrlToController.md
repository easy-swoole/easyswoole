# URL与控制器
## 控制器
控制器名称空间前缀统一为 "{$APPLICATION_DIR}\Controller,即系统默认应用目录为App，那么所有的控制器均应在/App/Controller目录下。 所有的控制器都应继承Core\AbstraceInterface\AbstractController。
### 关于AbstractController中的抽象方法
easyswoole中，任何控制器都需继承AbstractController，并实现其中的index,onRequest,actionNotFound、afterAction四个方法：
- index()
    
  控制器中默认存在方法，当在URL中无法解析出actionName时，将默认执行该方法。例如有一个Test控制器，当访问domain/test路径时，则默认解析为index。

- onRequest($actionName)
  
  当一个URL请求进来，能够被映射到控制器且做完actionName解析后，将立马执行OnRequest事件，以便对请求做预处理，如权限过滤等。注意，该事件与Conf/Event下的onRequest事件并不冲突（Conf/Event优先级最高）。
  
- actionNotFound($actionName = null, $arguments = null)

  当在URL中解析出actionName，而在控制器中无存在对应方法（函数）时，则执行该方法。例如有一个Test控制器，当访问domain/test/test1/index.html路径时，actionName会被解析为test1，而此时若控制器中无test1方法时，则执行actionNotFount。
- afterAction()
  
  在任何的控制器响应结束后，均会执行该事件,该事件预留于做分析记录。
  
### 关于AbstractController中的实体方法
- actionName()
  
  当一个URL请求进来，能够被映射到控制器时，那么将从该URL中解析出对应的行为名称，若无则默认为index。在控制器内的任意位置调用$this->actionName()均能获得当前行为名称。
  
- request()

  返回当前Core\Http\Request实例。
- response()
    
  返回当前Core\Http\Response。
  
## URL访问规则
仅支持 pathInfo 模式的 URL,且与控制器名称(方法)保持一致,控制器搜索规则为优先完整匹配模式。

例如访http://domain/api/index.html 则默认尝试优先搜索 App\Controller\Api\Index 控制器， 若无对应控制器则尝试搜索，App\Controller\Api 控制器，以上actionName均为 index ，再若无对应控制器则尝试搜索 App\Controller\Index 控制器，而此时，
actionName 则为 api 。具体示例代码请看 控制器用法 跑一遍便知。
    