<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/13
 * Time: 上午12:08
 */

namespace Core\AbstractInterface;


abstract class AbstractREST extends AbstractController
{
    function index()
    {
        // TODO: Implement index() method.
        $this->actionNotFound();
    }
    function __call($actionName, $arguments)
    {
        /*
         * restful中无需预防恶意调用控制器内置方法。
         */
        $actionName = $this->request()->getMethod().ucfirst($actionName);
        //执行onRequest事件
        $this->actionName($actionName);
        $this->onRequest($actionName);
        //判断是否被拦截
        if(!$this->response()->isEndResponse()){
            if(method_exists($this,$actionName)){
                $realName = $this->actionName();
                $this->$realName();
            }else{
                $this->actionNotFound($actionName, $arguments);
            }
        }
        $this->afterAction();
    }
}