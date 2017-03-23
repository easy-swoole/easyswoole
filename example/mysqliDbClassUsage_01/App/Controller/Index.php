<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 11:51
 */

namespace App\Controller;


use Conf\Config;
use Core\AbstractInterface\AbstractController;

class Index extends AbstractController
{
    function index()
    {
        // TODO: Implement index() method.
       $conf = Config::getInstance()->getConf("MYSQL");
       $db = new \MysqliDb($conf['HOST'],$conf['USER'],$conf['PASSWORD'], $conf['DB_NAME']);
       $this->response()->write($db->ping());
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
        $this->response()->write("this is index test");/*  url:domain/test/index.html  domain/test/   domain/test  */
    }

}