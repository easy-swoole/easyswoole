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

    abstract function client();

    abstract function actionNotFound(string $actionName);

    abstract function afterAction($actionName);

    function __construct(array $args)
    {
        $this->response = new SplStream();
        $this->args = $args;
    }

    /*
     * 返回false的时候为拦截
     */
    public function onRequest(string $actionName):bool
    {
        return true;
    }

    function getResponse():SplStream
    {
        return $this->response;
    }

    function write(string $message):void
    {
        $this->response->write($message);
    }

    public function getArg($key)
    {
        if(isset($this->args[$key])){
            return $this->args[$key];
        }else{
            return null;
        }
    }

    public function getArgs():array
    {
        return $this->args;
    }

    /**
     * @return string
     */
    public function getActionName():string
    {
        return $this->actionName;
    }

    /**
     * @param string $actionName
     */
    public function setActionName(string $actionName)
    {
        $this->actionName = $actionName;
    }

    public function __hook(string $actionName)
    {
        $this->actionName = $actionName;
        if($this->onRequest($actionName) !== false){
            if(method_exists($this,$actionName)){
                $forbidMethod = [
                    'client','actionNotFound','__construct','write','getArg','getActionName','getArgs','__hook',
                    'getResponse','afterAction'
                ];
                if(!in_array($this->getActionName(),$forbidMethod)){
                    $this->$actionName();
                    $this->afterAction($this->getActionName());
                }
            }else{
                $this->actionNotFound($actionName);
            }
        }
    }
}