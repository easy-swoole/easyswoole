<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/22
 * Time: 下午12:51
 */

namespace EasySwoole\Core\Component\Rpc\AbstractInterface;


use EasySwoole\Core\Component\Rpc\Common\ServiceCaller;
use EasySwoole\Core\Component\Rpc\Common\ServiceResponse;
use EasySwoole\Core\Component\Rpc\Common\Status;
use EasySwoole\Core\Socket\Client\Tcp;

abstract class AbstractRpcService
{

    private $client;
    private $serviceCaller;
    private $response;

    function __construct(Tcp $client,ServiceCaller $serviceCaller,ServiceResponse $response)
    {
        $this->client = $client;
        $this->serviceCaller = $serviceCaller;
        $this->response = $response;
        if($serviceCaller->getServiceAction() == '__construct'){
            $this->response->setStatus(Status::SERVICE_REJECT_REQUEST);
        }else{
            $this->__hook($serviceCaller->getServiceAction());
        }
    }

    abstract function index();

    protected function actionNotFound($action):void
    {
        $this->response->setStatus(Status::SERVICE_ACTION_NOT_FOUND);
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
        return $this->serviceCaller->getServiceAction();
    }

    protected function __hook(?string $actionName):void
    {
        if($this->onRequest($actionName) !== false){
            //支持在子类控制器中以private，protected来修饰某个方法不可见
            try{
                $ref = new \ReflectionClass(static::class);
                if($ref->hasMethod($actionName) && $ref->getMethod( $actionName)->isPublic()){
                    $this->$actionName();
                }else{
                    $this->actionNotFound($actionName);
                }
            }catch (\Throwable $throwable){
                $this->onException($throwable,$actionName);
            }
            //afterAction 始终都会被执行
            try{
                $this->afterAction($actionName);
            }catch (\Throwable $throwable){
                $this->onException($throwable,$actionName);
            }
        }
    }

    protected function serviceCaller():ServiceCaller
    {
        return $this->serviceCaller;
    }

    protected function response():ServiceResponse
    {
        return $this->response;
    }
}