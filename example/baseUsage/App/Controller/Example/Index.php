<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/25
 * Time: 下午4:52
 */

namespace App\Controller\Example;


use Core\AbstractInterface\AbstractController;
use Core\Component\Di;
use Core\Component\Logger;
use Core\Http\Message\Status;
use Core\Swoole\AsyncTaskManager;

class Index extends AbstractController
{
    function index()
    {
        // TODO: Implement index() method.
    }

    function onRequest($actionName)
    {
        // TODO: Implement onRequest() method.
    }

    function actionNotFount($actionName = null, $arguments = null)
    {
        // TODO: Implement actionNotFount() method.
        $this->response()->withStatus(Status::CODE_NOT_FOUND);
    }

    function afterAction()
    {
        // TODO: Implement afterResponse() method.
    }
    function di(){
        $test = Di::getInstance()->get("DiTest")->test();
        $this->response()->write($test);
    }
    function async(){
        AsyncTaskManager::getInstance()->add(function (){
           Logger::log("async add");
        });
        $this->response()->write("add async");
    }

}