<?php

/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/24
 * Time: 下午3:11
 */
namespace App2\Controller;

use Core\AbstractInterface\AbstractController;

class Index extends AbstractController
{

    function index()
    {
        // TODO: Implement index() method.
        $this->response()->write("app2");
    }

    function onRequest($actionName)
    {
        // TODO: Implement onRequest() method.
    }

    function actionNotFound($actionName = null, $arguments = null)
    {
        // TODO: Implement actionNotFound() method.
    }

    function afterAction()
    {
        // TODO: Implement afterAction() method.
    }
}