<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/24
 * Time: 下午11:30
 */

namespace Core\Utility\Curl;


class Multi
{
    private $taskList = [];
    function addRequest($taskName){
        $request = new Request();
        $this->taskList[$taskName] = $request;
        return $request;
    }

    function select(){
        $successCh = null;
        $mh = curl_multi_init();
        $map = array();
        foreach ($this->taskList as $key => $item){
            $ch = curl_init();
            curl_setopt_array($ch,$item->getOpt());
            curl_multi_add_handle($mh,$ch);
            $map[$key] = $ch;
        }
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($active && $mrc == CURLM_OK) {
            do {
                $mrc = curl_multi_exec($mh, $active);
                if(curl_multi_select($mh) != -1){
                    if ($mrc == CURLM_OK)
                    {
                        while($info = curl_multi_info_read($mh))
                        {
                            $successCh = $info['handle'];
                            $mrc = $active = null;
                        }
                    }
                }
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        foreach ($map as $ch){
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        if($successCh){
            $res = new Response(curl_multi_getcontent($successCh),$successCh);
        }else{
            $res = null;
        }
        curl_multi_close($mh);
        return $res;
    }

    function exec(){
        $mh = curl_multi_init();
        $map = array();
        // 增加2个句柄
        foreach ($this->taskList as $key => $item){
            $ch = curl_init();
            curl_setopt_array($ch,$item->getOpt());
            curl_multi_add_handle($mh,$ch);
            $map[$key] = $ch;
        }

        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($active && $mrc == CURLM_OK) {
            do {
                $mrc = curl_multi_exec($mh, $active);
                if(curl_multi_select($mh) != -1){
                    if ($mrc == CURLM_OK)
                    {
                        while($info = curl_multi_info_read($mh))
                        {
                            curl_multi_remove_handle($mh, $info['handle']);
                        }
                    }
                }
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
        $data = array();
        foreach ($map as $key => $ch){
            curl_multi_remove_handle($mh,$ch);
            //ch在Response结构函数中已经自动被close
            $data[$key] = new Response(curl_multi_getcontent($ch),$ch);
        }
        curl_multi_close($mh);
        return $data;
    }

}