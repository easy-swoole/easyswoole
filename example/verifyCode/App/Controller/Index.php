<?php

/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/20
 * Time: 下午3:38
 */
namespace App\Controller;

use App\Utility\VerifyCode\Verify;
use Core\AbstractInterface\AbstractController;
use Core\Utility\Random;

class Index extends AbstractController
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
        $this->response()->withStatus(404);
    }

    function afterAction()
    {
        // TODO: Implement afterAction() method.
    }
    function verify(){
        $code = Verify::create(Random::randStr(4));
        $this->response()->write($code->getImageSting());
        $this->response()->withHeader("Content-type",$code->getImageMineType());
        $this->response()->setCookie("imageCode",$code->getCodeStr());
    }
}