<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/20
 * Time: 下午10:26
 */

namespace App\Model;


use Core\Component\Barrier;
use Core\Component\Logger;
use Core\Utility\Curl\Request;

class FastDownload
{
    function download($fileUrl,$taskNum = 4){
        //先获得文件的总长度
        $req = new Request($fileUrl);
        $req->setOpt(array(
            CURLOPT_NOBODY=>true,
            CURLOPT_HEADER=>1
        ));
        $msg = $req->exec()->getHeader();
        preg_match( "/Content-Length: (\d+)/", $msg, $matches);
        //计算分区块
        $length = (int)$matches[1];
        $partSize = intval($length/$taskNum);
        $rangList = array();
        for($i=1;$i < $taskNum;$i++){
            $rangList[] = array(
                ($i-1)*$partSize,$i*$partSize-1
            );
        }
        $rangList[] = array(
            ($i-1)*$partSize,$length
        );
        //添加任务
        $barrier = new Barrier();
        foreach ($rangList as $key => $item){
            $barrier->add($key,function ()use($item,$fileUrl){
                $req = new Request($fileUrl);
                $req->setOpt(array(
                    CURLOPT_RANGE=>$item[0]."-".$item[1]
                ));
                return $req->exec()->getBody();
            });
        }
        //60秒
        $ret = $barrier->run(60);
        return implode('',$ret);
    }
}