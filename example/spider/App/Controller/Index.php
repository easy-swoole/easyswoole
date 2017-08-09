<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 11:51
 */

namespace App\Controller;


use App\Model\Queue;
use App\Model\TaskBean;
use App\Task;
use App\Utility\SysConst;
use Core\AbstractInterface\AbstractController;
use Core\Component\Barrier;
use Core\Component\Logger;
use Core\Component\ShareMemory;
use Core\Http\Message\Status;
use Core\Swoole\AsyncTaskManager;
use Core\Swoole\SwooleHttpServer;
use Core\UrlParser;

class Index extends AbstractController
{
    function index()
    {
        // TODO: Implement index() method.
        $this->response()->withHeader("Content-type","text/html;charset=utf-8");
        $this->response()->write('
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        div{ padding: 4px 48px;}
        body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px}
        h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; }
        p{ line-height: 1.8em; font-size: 36px } a,a:hover{color:blue;}
    </style>
    <div style="padding: 24px 48px;">
        <h1>:)</h1><p>欢迎使用<b> easySwoole</b></p><br/>
    </div>
 ');/*  url:domain/index.html  domain/   domain  */
    }

    function onRequest($actionName)
    {
        // TODO: Implement onRequest() method.
    }

    function actionNotFound($actionName = null, $arguments = null)
    {
        // TODO: Implement actionNotFount() method.
        $this->response()->withStatus(Status::CODE_NOT_FOUND);
    }

    function afterAction()
    {
        // TODO: Implement afterResponse() method.
    }

    function addTask(){
        $url = $this->request()->getRequestParam("url");
        if(empty($url)){
            $url = 'http://wiki.swoole.com/';
        }
        $bean = new TaskBean();
        $bean->setUrl($url);
        //做异步投递
        AsyncTaskManager::getInstance()->add(function ()use($bean){
           Queue::set($bean);
        });
        $this->response()->writeJson(200,null,"任务投递成功");
    }
    function status(){
        $num = ShareMemory::getInstance()->get(SysConst::TASK_RUNNING_NUM);
        $this->response()->writeJson(200,array(
           "taskRuningNum"=>$num
        ));
    }
}
