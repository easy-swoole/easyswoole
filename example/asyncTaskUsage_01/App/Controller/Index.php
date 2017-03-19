<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 11:51
 */

namespace App\Controller;


use App\AsyncTask;
use Core\AbstractInterface\AbstractController;
use Core\Component\Logger;
use Core\Swoole\AsyncTaskManager;

class Index extends AbstractController
{
    function index()
    {
        // TODO: Implement index() method.
        $this->response()->write("this is index");/*  url:domain/index.html  domain/   domain  */
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
    function async1(){
        AsyncTaskManager::getInstance()->add(new AsyncTask());
    }
    function async2(){
        AsyncTaskManager::getInstance()->add(function (){
            Logger::console("my async");
            return 1;
        },AsyncTaskManager::TASK_DISPATCHER_TYPE_RANDOM,function ($data){
            Logger::console("my async finish callback data {$data}");
        });
    }

}