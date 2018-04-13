<?php
/**
 * Created by PhpStorm.
 * User: 111
 * Date: 2018/4/13
 * Time: 19:09
 */

namespace EasySwoole\Test;


class BaseControllerTest
{
    /**
     * jsonp输出
     * @param string $callback
     * @param null $data
     * @param int $code
     * @param null $msg
     * @param int $status
     * @return bool|string
     */
    public function writeJsonp($callback, $data = null, $msg = null, $code = 200,  $status = 200){
        $data = Array(
            "code"=> $code,
            "data"=> $data,
            "msg"=> $msg
        );
        $output = json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $output = $callback . "({$output})";
//            $this->response()->write($output);
//            $this->response()->withHeader('Content-type','text/javascript; charset=utf-8');
//            $this->response()->withStatus($status);
        return $output;
    }
}

$bsTest = new BaseControllerTest();
echo $bsTest->writeJsonp("function", ["js" => 1], "test");
