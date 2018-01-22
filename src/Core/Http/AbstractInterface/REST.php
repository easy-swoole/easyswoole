<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/29
 * Time: 下午7:27
 */

namespace EasySwoole\Core\Http\AbstractInterface;


use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

abstract class REST extends Controller
{
    /*
       *支持方法
        'GET',      // 从服务器取出资源（一项或多项）
       'POST',     // 在服务器新建一个资源
       'PUT',      // 在服务器更新资源（客户端提供改变后的完整资源）
       'PATCH',    // 在服务器更新资源（客户端提供改变的属性）
       'DELETE',   // 从服务器删除资源
       'HEAD',     // 获取 head 元数据
       'OPTIONS',  // 获取信息，关于资源的哪些属性是客户端可以改变的
     */
    function __construct($actionName, Request $request, Response $response)
    {
        $actionName = $request->getMethod().ucfirst($actionName);
        parent::__construct($actionName, $request, $response);
    }

    function index()
    {
        $this->actionNotFound();
    }
}