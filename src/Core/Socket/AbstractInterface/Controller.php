<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/29
 * Time: 下午7:32
 */

namespace EasySwoole\Core\Socket\AbstractInterface;

use EasySwoole\Core\Component\Spl\SplStream;
use EasySwoole\Core\Socket\Common\CommandBean;

abstract class Controller
{
    private $response;
    private $request;

    protected abstract function client();

    function __construct(CommandBean $request,SplStream $response)
    {
        $this->request = $request;
        $this->response = $response;
        if($request->getAction() != '__construct'){
            $this->__hook($request->getAction());
        }else{
            $response->write('do not try to call __construct');
        }
    }

    protected function actionNotFound(?string $actionName)
    {

    }

    protected function afterAction($actionName)
    {

    }

    protected function onException(\Throwable $throwable):void
    {
        throw $throwable;
    }



    /*
     * 返回false的时候为拦截
     */
    protected function onRequest(?string $actionName):bool
    {
        return true;
    }

    protected function response()
    {
        return $this->response;
    }

    protected function request():CommandBean
    {
        return $this->request;
    }


    protected function __hook(?string $actionName)
    {
        //这里面的class一定存在
        if($this->onRequest($actionName) === false){
            return;
        }
        $actionName = $this->request->getAction();
        $ref = new \ReflectionClass(static::class);
        if($ref->hasMethod($actionName)){
            if($ref->getMethod($actionName)->isPublic()){
                try{
                    $this->$actionName();
                    $this->afterAction($actionName);
                }catch (\Throwable $throwable){
                    $this->onException($throwable);
                }
            }else{
                $this->actionNotFound($actionName);
            }
        }else{
            $this->actionNotFound($actionName);
        }
    }
}