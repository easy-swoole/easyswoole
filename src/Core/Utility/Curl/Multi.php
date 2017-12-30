<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/26
 * Time: 下午1:22
 */

namespace EasySwoole\Core\Utility\Curl;


class Multi
{
    private $taskList = [];

    public function addTask($taskName):Request
    {
        $this->taskList[$taskName] = new Request();
        return $this->taskList[$taskName];
    }

    public function select($timeout = 1000):?Response
    {
        $successCh = null;
        $mh = curl_multi_init();
        $map = array();
        foreach ($this->taskList as $key => $item){
            $ch = curl_init();
            curl_setopt_array($ch,$item->getOpt());
            curl_multi_add_handle($mh,$ch);
            $map[(int)$ch] = $ch;
        }
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        $takeTime = 0;
        while ($active && $mrc == CURLM_OK) {
            do {
                $mrc = curl_multi_exec($mh, $active);
                $info = curl_multi_info_read($mh);
                if(!empty($info)){
                    $successCh = $info['handle'];
                    //跳出循环并删除成功的ch
                    curl_multi_remove_handle($mh,$successCh);
                    unset($map[(int)$successCh]);
                    $mrc = $active = null;
                }
                $takeTime++;
                usleep(1000);
                if($takeTime > $timeout){
                    $mrc = $active = null;
                }
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
        if($successCh){
            $res = new Response(curl_multi_getcontent($successCh),$successCh);
        }else{
            $res = null;
        }
        foreach ($map as $ch){
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
        return $res;
    }

    public function exec($timeout = 1000):array
    {
        $res = [];
        $successChs = [];
        $mh = curl_multi_init();
        $map = array();
        $allChs = [];
        foreach ($this->taskList as $key => $item){
            $ch = curl_init();
            curl_setopt_array($ch,$item->getOpt());
            curl_multi_add_handle($mh,$ch);
            $map[(int)$ch] = $key;
            $allChs[(int)$ch] = $ch;
        }
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        $takeTime = 0;
        while ($active && $mrc == CURLM_OK) {
            do {
                $mrc = curl_multi_exec($mh, $active);
                $info = curl_multi_info_read($mh);
                if($info){
                    $successChs[(int)$info['handle']] = $info['handle'];
                    curl_multi_remove_handle($mh, $info['handle']);
                }
                $takeTime++;
                usleep(1000);
                if($takeTime > $timeout){
                    $mrc = $active = null;
                }
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
        foreach ($successChs as $successChIndex => $successCh){
            $taskName = $map[$successChIndex];
            unset($allChs[$successChIndex]);
            $res[$taskName] = new Response(curl_multi_getcontent($successCh),$successCh);
        }
        foreach ($allChs as $ch){
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
        return $res;
    }
}