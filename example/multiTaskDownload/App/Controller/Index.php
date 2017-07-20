<?php

/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/20
 * Time: 下午3:38
 */
namespace App\Controller;

use App\Model\FastDownload;
use App\Utility\VerifyCode\Verify;
use Core\AbstractInterface\AbstractController;
use Core\Component\Di;
use Core\Component\SysConst;
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
    function download(){
        $test = new FastDownload();
        //请确保你本地的Apache或者nginx服务器下，有a.zip这个文件存在
        $data = $test->download('http://localhost/a.zip');
        file_put_contents(Di::getInstance()->get(SysConst::TEMP_DIRECTORY)."/a.zip",$data);
        $this->response()->write("success");
    }
}