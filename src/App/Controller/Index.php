<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 11:51
 */

namespace App\Controller;


use Core\AbstractInterface\AbstractController;

class Index extends AbstractController
{
    function index()
    {
        // TODO: Implement index() method.
        $this->response()->sendHeader("Content-type","text/html;charset=utf-8");
        $this->response()->write('
    <style type="text/css">
        *{ padding: 0; margin: 0; } 
        div{ padding: 4px 48px;} 
        body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} 
        h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } 
        p{ line-height: 1.8em; font-size: 36px } a,a:hover{color:blue;}
    </style>
    <div style="padding: 24px 48px;">
        <h1>:)</h1><p>欢迎使用<b> easyPHP-Swoole</b></p><br/>
    </div>
 ');/*  url:domain/index.html  domain/   domain  */
    }

    function onRequest($actionName)
    {
        // TODO: Implement onRequest() method.
    }

    function actionNotFount($actionName = null, $arguments = null)
    {
        // TODO: Implement actionNotFount() method.
    }

    function afterResponse()
    {
        // TODO: Implement afterResponse() method.
    }
    function test(){
        $this->response()->write("this is index test");/*  url:domain/test/index.html  domain/test/   domain/test  */
    }

}