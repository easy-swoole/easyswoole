<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午1:07
 */

namespace Core\AbstractInterface;

use Core\Http\Message\Status;
use Core\Http\Request;
use Core\Http\Response;

abstract class AbstractController
{
    protected $actionName = null;
    protected $callArgs = null;
    function actionName($actionName = null){
        if($actionName === null){
            return $this->actionName;
        }else{
            $this->actionName = $actionName;
        }
    }
    abstract function index();
    abstract function onRequest($actionName);
    abstract function actionNotFound($actionName = null, $arguments = null);
    abstract function afterAction();
    function request(){
        return Request::getInstance();
    }
    function response(){
        return Response::getInstance();
    }
    function __call($actionName, $arguments)
    {
        // TODO: Implement __call() method.
        /*
           * 防止恶意调用
           * actionName、onRequest、actionNotFound、afterAction、request
           * response、__call
        */
        if(in_array($actionName,array(
            'actionName','onRequest','actionNotFound','afterAction','request','response','__call'
        ))){
            $this->response()->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
            return;
        }
        //执行onRequest事件
        $this->actionName($actionName);
        $this->onRequest($actionName);
        //判断是否被拦截
        if(!$this->response()->isEndResponse()){
            $realName = $this->actionName();
            if(method_exists($this,$realName)){
                $this->$realName();
            }else{
                $this->actionNotFound($realName, $arguments);
            }
        }
        $this->afterAction();
    }
}