<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/5
 * Time: 下午12:57
 */

namespace App\Controller\Api;


use Core\AbstractInterface\AbstractController;
use Core\Http\Message\Status;

class Auth extends AbstractController
{

    function index()
    {
        // TODO: Implement index() method.
        $this->actionNotFound();
    }

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
    function login(){
        /*
         * url is /api/auth/login/index.html
         */
        $this->response()->writeJson(Status::CODE_OK,null,'this is auth login ');
    }
}