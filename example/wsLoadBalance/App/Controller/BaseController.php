<?php
namespace App\Controller;


use App\Utility\Smarty;
use Core\AbstractInterface\AbstractController;
use Core\Http\Message\Status;
use Conf\Config;
use App\Utility\Security;
/**
 * 控制器基类
 * User: liu
 * Date: 2017/7/17
 */
abstract class BaseController extends AbstractController {
    function index() {
        // TODO: Implement index() method.
        $this->actionName();
    }

    function onRequest($actionName) {
        // TODO: Implement onRequest() method.
    }

    function actionNotFound($actionName = null, $arguments = null) {
        // TODO: Implement actionNotFount() method.
        $this->response()->withStatus(Status::CODE_NOT_FOUND);
    }

    function afterAction() {
        // TODO: Implement afterResponse() method.
    }

    function smartyDisplay($tpl, $vars = array()) {
        $smarty = new Smarty();
        foreach ($vars as $key => $value) {
            $smarty->assign($key, $value);
        }
        $this->response()->write($smarty->getDisplayString($tpl));
    }
}