<?php
namespace App\HttpController\Api;

class Test extends BaseController
{


    function index()
    {
        // TODO: Implement index() method.
    }

    function holdup(){
    }


    function test(){
//        for ($i=0; $i<1000; $i++){
//            \go(function () {
//                \co::sleep(1);
//                echo "hello";
//            });
//        }
        $number = rand(0, 10);
        $output = $this->writeJson(200, $number);
        $this->setCache($output);
    }

}