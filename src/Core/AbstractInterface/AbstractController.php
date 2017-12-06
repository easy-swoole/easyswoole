<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午6:35
 */

namespace EasySwoole\Core\AbstractInterface;


use EasySwoole\Core\Http\Message\Status;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

abstract class AbstractController
{
    private $request;
    private $response;
    private $actionName;
    private static $forbidMethod = [
        'getActionName','onRequest','actionNotFound','afterAction','request','response','__call','__hook'
    ];

    abstract function index();

    abstract function onRequest($action):void;

    abstract function actionNotFound($action):void;

    abstract function afterAction($actionName):void;

    public function getActionName():string
    {
        return $this->actionName;
    }

    public function resetAction(string $action):void
    {
        $this->actionName = $action;
    }

    public function __hook(string $actionName,Request $request,Response $response):void
    {
        $this->request = $request;
        $this->response = $response;
        $this->actionName = $actionName;
        //防止恶意调用
        if(in_array($actionName,self::$forbidMethod)){
            $response->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
            $response->end();
        }else{
            if(!$this->response->isEndResponse()){
                $this->onRequest($actionName);
                //防止onRequest中   对actionName 进行修改
                $actionName = $this->actionName;
                $this->$actionName();
                $this->afterAction($actionName);
            }
        }
    }

    final public function request():Request
    {
        return $this->request;
    }

    final public function response():Response
    {
        return $this->response;
    }

    final function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        $this->actionNotFound($name);
    }
}