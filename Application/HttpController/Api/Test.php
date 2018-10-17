<?php
namespace App\HttpController\Api;

use App\Vendor\Db\CoMysqlPool;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

/**
 * 注意: 控制器命名遵循psr规范 首字母大写，同时与文件名一致。router里的路由处理器,也要对应文件名大小写
 * Class Test
 * @package App\HttpController\Api
 */
class Test extends BaseController
{

    function __construct(string $actionName, Request $request, Response $response)
    {
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
//        $posts = $this->request()->getRequestParam();
//        $validator = new Validator();
//        $validator->validate($posts, array(
//            "input" => "min:3|max:5|required",
//            "input2" => "numeric",
//            "input3" => "mobile",
//            "input4" => "json"
//        ), array(
//            "input" => "input必须3-5之间",
//            "input2" => "必须是数值型",
//            "input3" => "手机号码格式不对"
//        ));
//        $res = $this->redis->get("test")
//        $res = TestModel::getInstance()->test();
        $this->response()->write("hello man");
    }

    function log(){
        $data = array(1,2,3,4,5);
        $this->logger->debug($params, array(
            "file" => __DIR__ . "/" . __FILE__,
            "line" => __LINE__,
            "class" => __CLASS__,
            "method" => __METHOD__
        ));
    }


}