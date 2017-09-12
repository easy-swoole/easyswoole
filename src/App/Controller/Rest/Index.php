<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/13
 * Time: 上午12:03
 */

namespace App\Controller\Rest;


use Core\AbstractInterface\AbstractREST;
use Core\Http\Message\Status;

class Index extends AbstractREST
{

    function onRequest($actionName)
    {
        // TODO: Implement onRequest() method.
    }

    function actionNotFound($actionName = null, $arguments = null)
    {
        // TODO: Implement actionNotFound() method.
        $this->response()->withStatus(Status::CODE_NOT_FOUND);
    }

    function afterAction()
    {
        // TODO: Implement afterAction() method.
    }

    function GETIndex(){
        $this->response()->write("this is REST GET Index");
    }
    function POSTIndex(){
        $this->response()->write("this is REST POST Index");
    }

    function GETTest(){
        $this->response()->write("this is REST GET test");
    }
    function POSTTest(){
        $this->response()->write("this is REST POST test");
    }

}