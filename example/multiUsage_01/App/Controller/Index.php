<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 11:51
 */

namespace App\Controller;


use App\Utility\Smarty;
use Core\AbstractInterface\AbstractController;
use Core\Http\Message\Status;
use Core\Swoole\SwooleHttpServer;


class Index extends AbstractController
{
    function index()
    {
        // TODO: Implement index() method.
        /*  url:domain/index.html  domain/   domain  */
        $smarty = new Smarty();
        $smarty->assign("time",time());
        $this->response()->write($smarty->getDisplayString('index.html'));
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

    function reload(){
        $this->response()->write("going to reload");
        SwooleHttpServer::getInstance()->getServer()->reload();
    }

    function router(){
        $this->response()->write("your router not end");
    }

}