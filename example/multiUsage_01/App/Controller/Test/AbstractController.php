<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/7
 * Time: 下午11:47
 */

namespace App\Controller\Test;


use App\Utility\Smarty;
use Core\AbstractInterface\AbstractController as Base;
use Core\Http\Message\Status;

abstract class AbstractController extends Base
{

    function index()
    {
        // TODO: Implement index() method.
        $this->actionName();
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
    function smartyDisplay($tpl,$vars = array()){
        $smarty = new Smarty();
        foreach ($vars as $key => $value){
            $smarty->assign($key,$value);
        }
        $this->response()->write($smarty->getDisplayString($tpl));
    }
}