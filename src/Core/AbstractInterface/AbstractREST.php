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
    /*
     * 支持方法
         'GET',      // 从服务器取出资源（一项或多项）
        'POST',     // 在服务器新建一个资源
        'PUT',      // 在服务器更新资源（客户端提供改变后的完整资源）
        'PATCH',    // 在服务器更新资源（客户端提供改变的属性）
        'DELETE',   // 从服务器删除资源
        'HEAD',     // 获取 head 元数据
        'OPTIONS',  // 获取信息，关于资源的哪些属性是客户端可以改变的
     */
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