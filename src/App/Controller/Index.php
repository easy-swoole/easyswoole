<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 11:51
 */

namespace App\Controller;


use App\Task;
use Core\AbstractInterface\AbstractController;
use Core\Component\Barrier;
use Core\Component\Logger;
use Core\Http\Message\Status;
use Core\Swoole\AsyncTaskManager;
use Core\Swoole\Server;
use Core\UrlParser;

class Index extends AbstractController
{
    function index()
    {
        // TODO: Implement index() method.
        $this->response()->withHeader("Content-type","text/html;charset=utf-8");
        $this->response()->write(file_get_contents(ROOT."/App/Static/index.html"));
    }

    function onRequest($actionName)
    {
        // TODO: Implement onRequest() method.
    }

    function actionNotFound($actionName = null, $arguments = null)
    {
        // TODO: Implement actionNotFount() method.
        $this->response()->withStatus(Status::CODE_NOT_FOUND);
        $this->response()->write(file_get_contents(ROOT."/App/Static/404.html"));
    }

    function afterAction()
    {
        // TODO: Implement afterResponse() method.
    }
    function test(){
       $this->response()->write("this is test");
    }

    function test2(){
        $this->response()->write("this is test2");
    }
    function test3(){
        $this->response()->write("this is test2");
    }

    function shutdown(){
        Server::getInstance()->getServer()->shutdown();
    }
    function router(){
        $this->response()->write("your router not end");
    }

}