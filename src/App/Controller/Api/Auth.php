<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/15
 * Time: 下午8:22
 */

namespace App\Controller\Api;


use Core\AbstractInterface\AbstractController;

class Auth extends AbstractController
{

    function index()
    {
        // TODO: Implement index() method.
        $this->response()->write("this is api auth index");/*  url:domain/api/auth/index.html  domain/api/auth/   */
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
      var_dump($this->request()->getUploadedFiles());
    }
}