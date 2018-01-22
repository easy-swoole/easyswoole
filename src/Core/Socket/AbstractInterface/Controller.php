<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/29
 * Time: 下午7:32
 */

namespace EasySwoole\Core\Socket\AbstractInterface;

use EasySwoole\Core\Socket\Common\CommandBean;

abstract class Controller
{
    private $response;
    private $actionName;
    private $args;

    protected abstract function client();

    protected function actionNotFound(string $actionName)
    {

    }

    protected function afterAction($actionName)
    {

    }

    protected function onException(\Throwable $throwable):void
    {
        throw $throwable;
    }

    protected function __construct(array $args)
    {
        $this->response = new CommandBean();
        $this->args = $args;
    }

    /*
     * 返回false的时候为拦截
     */
    protected function onRequest(string $actionName):bool
    {
        return true;
    }

    protected function response():CommandBean
    {
        return $this->response;
    }

    protected function getArg($key)
    {
        if(isset($this->args[$key])){
            return $this->args[$key];
        }else{
            return null;
        }
    }

    protected function getArgs():array
    {
        return $this->args;
    }

    /**
     * @return string
     */
    protected function getActionName():string
    {
        return $this->actionName;
    }

    /**
     * @param string $actionName
     */
    protected function setActionName(string $actionName)
    {
        $this->actionName = $actionName;
    }

    public function __hook(string $actionName):?CommandBean
    {
        if($actionName == '__hook'){
            return null;
        }
        $this->actionName = $actionName;
        if($this->onRequest($actionName) !== false){
            $ref = new \ReflectionClass(static::class);
            if($ref->hasMethod($actionName)){
                if($ref->getMethod($actionName)->isPublic()){
                    try{
                        $this->$actionName();
                        $this->afterAction($this->getActionName());
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
        return $this->response();
    }
}