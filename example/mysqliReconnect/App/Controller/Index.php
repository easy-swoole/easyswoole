<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/26
 * Time: 上午10:11
 */

namespace App\Controller;


use App\Utility\Mysqli;
use App\Utility\SysConst;
use Core\AbstractInterface\AbstractController;
use Core\Component\Di;

class Index extends AbstractController
{

    function index()
    {
        // TODO: Implement index() method.
        $mysql = Di::getInstance()->get(SysConst::MYSQL);
        if($mysql instanceof Mysqli){
            $this->response()->writeJson(200,$mysql->get("item_list"));
        }
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