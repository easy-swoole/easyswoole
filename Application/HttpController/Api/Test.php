<?php
namespace App\HttpController\Api;

use App\Model\TestModel;
use App\Utility\Security;
use App\Utility\Utils;
use App\Utility\Validator;
use App\Vendor\Db\CoMysqlPool;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;
use Kjcx\HelloReply;
use Kjcx\HelloRequest;

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
        $raw = $this->request()->getBody()->__toString();
        $req = new HelloRequest();
        $req->mergeFromString($raw);
        $name = $req->getName();
        $desc = $req->getDesc();
        $res = new HelloReply();
        $res->setMessage("ddads");
        $res->setData("dasdas");
        $this->response()->write($res->serializeToString());
    }

    function testCoMysql(){
        $mysql1 = CoMysqlPool::getInstance()->getConnect();
        $mysql1->setDefer();
        $res = $mysql1->query("select * from test as total");
        $mysql_res = $mysql1->recv();
        var_dump($mysql_res);
        CoMysqlPool::getInstance()->freeConnect($mysql1);
    }

    function sendProtobuf(){
        $req = new HelloRequest();
        $req->setName("hello proto");
        $req->setDesc("good desc");
        $str = $req->serializeToString();
        $client = new \GuzzleHttp\Client();
        $res = $client->request("POST", "http://localhost:9501/", [
            "body" => $str
        ]);
        $contents = $res->getBody()->getContents();
        $res = new HelloReply();
        $res->mergeFromString($contents);
        var_dump($res->getData());
        var_dump($res->getMessage());
    }

}