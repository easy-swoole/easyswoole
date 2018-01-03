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

    protected abstract function index();

    protected abstract function actionNotFound($action = null):void;

    protected abstract function afterAction($actionName):void;

    protected abstract function onException(\Exception $exception,$actionName):void;

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

    protected function __hook(string $actionName,Request $request,Response $response):void
    {
        $this->request = $request;
        $this->response = $response;
        $this->actionName = $actionName;
        if($this->onRequest($actionName) !== false){
            $this->onRequest($actionName);
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

    final protected function request():Request
    {
        return $this->request;
    }

    final protected function response():Response
    {
        return $this->response;
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