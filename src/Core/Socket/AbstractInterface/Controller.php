<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/29
 * Time: 下午7:32
 */

namespace EasySwoole\Core\Socket\AbstractInterface;


use EasySwoole\Core\Component\Spl\SplStream;

abstract class Controller
{
    private $response;
    private $actionName;
    private $args;

    protected abstract function client();

    protected abstract function actionNotFound(string $actionName);

    protected abstract function afterAction($actionName);

    protected abstract function onException(\Exception $exception):?string ;

    protected function __construct(array $args)
    {
        $this->response = new SplStream();
        $this->args = $args;
    }

    /*
     * 返回false的时候为拦截
     */
    protected function onRequest(string $actionName):bool
    {
        return true;
    }

    protected function response():SplStream
    {
        return $this->response;
    }

    protected function write(string $message):void
    {
        $this->response->write($message);
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

    public function __hook(string $actionName):?string
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
                    }catch (\Exception $exception){
                        return $this->onException($exception);
                    }
                }else{
                    $this->actionNotFound($actionName);
                }
            }else{
                $this->actionNotFound($actionName);
            }
        }
        return $this->response()->__toString();
    }
}