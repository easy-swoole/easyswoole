<?php
namespace App\HttpController\Api;

use App\Model\TestModel;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

class Test extends BaseController
{
    /**
     * @var TestModel
     */
    private $testModel;

    function __construct(string $actionName, Request $request, Response $response)
    {
        $this->testModel = new TestModel();
        parent::__construct($actionName, $request, $response);
    }

    function index()
    {
        // TODO: Implement index() method.
    }

    function test(){
//        for ($i=0; $i<1000; $i++){
//            \go(function () {
//                \co::sleep(1);
//                echo "hello";
//            });
//        }
//        $res = $this->testModel->test2();
        $res = "hello world";
        $this->writeJson(200, $res);
    }

}