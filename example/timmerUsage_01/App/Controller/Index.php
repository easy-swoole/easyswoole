<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 11:51
 */

namespace App\Controller;


use Core\AbstractInterface\AbstractController;
use Core\Component\Logger;
use Core\Swoole\Timer;

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
    function loop(){
        Timer::loop(8000,function (){
            Logger::console("loop timmer add by controller action");
        });
    }
    function after(){
        //8秒后执行
        Timer::delay(8000,function (){
            Logger::console("delay timmer add by controller action");
        });
    }
}