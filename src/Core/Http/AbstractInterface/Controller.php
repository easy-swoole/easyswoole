<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/29
 * Time: 下午7:26
 */

namespace EasySwoole\Core\Http\AbstractInterface;



use EasySwoole\Core\Http\Message\Status;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;
use EasySwoole\Core\Utility\Validate\Rules;
use EasySwoole\Core\Utility\Validate\Validate;

abstract class Controller
{
    private $request;
    private $response;
    private $actionName;

    abstract function index();

    public function __construct(string $actionName,Request $request,Response $response)
    {
        if($actionName == '__hook'){
            $this->response()->withStatus(Status::CODE_BAD_REQUEST);
        }else{
            $this->actionName = $actionName;
            $this->__hook( $request, $response);
        }
    }

    protected function actionNotFound($action = null):void
    {
        $this->response()->withStatus(Status::CODE_NOT_FOUND);
    }

    protected function afterAction($actionName):void
    {

    }

    protected function onException(\Throwable $throwable,$actionName):void
    {
        throw $throwable ;
    }

    protected function onRequest($action):?bool
    {
        return true;
    }

    protected function getActionName():string
    {
        return $this->actionName;
    }

    protected function resetAction(string $action):void
    {
        $this->actionName = $action;
    }

    protected function __hook(Request $request,Response $response):void
    {
        $this->request = $request;
        $this->response = $response;
        if($this->onRequest($this->actionName) !== false){
            //防止onRequest中   对actionName 进行修改
            $actionName = $this->actionName;
            //支持在子类控制器中以private，protected来修饰某个方法不可见
            $ref = new \ReflectionClass(static::class);
            if($ref->hasMethod($actionName) && $ref->getMethod($actionName)->isPublic()){
                try{
                    $this->$actionName();
                }catch (\Exception $exception){
                    $this->onException($exception,$actionName);
                }
                $this->afterAction($actionName);
            }else{
                $this->actionNotFound($actionName);
            }
        }
    }

    protected function request():Request
    {
        return $this->request;
    }

    protected function response():Response
    {
        return $this->response;
    }

    protected function writeJson($statusCode = 200,$result = null,$msg = null){
        if(!$this->response()->isEndResponse()){
            $data = Array(
                "code"=>$statusCode,
                "result"=>$result,
                "msg"=>$msg
            );
            $this->response()->write(json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type','application/json;charset=utf-8');
            $this->response()->withStatus($statusCode);
            return true;
        }else{
            trigger_error("response has end");
            return false;
        }
    }

    /*
     * 若不想用自带验证器，可以自己新建base控制器，重写validateParams方法
     */
    protected function validateParams(Rules $rules)
    {
        $validate = new Validate();
        return $validate->validate($this->request()->getRequestParam(),$rules);
    }
}