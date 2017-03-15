<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 下午3:59
 */

namespace Core\Utility\Curl;


class Curl
{
    static function request(Request $request,$autoDebug = 1){
        $curl = curl_init();
        curl_setopt_array($curl,$request->getOpt());
        $result = curl_exec($curl);
        if($autoDebug){
            $info = curl_getinfo($curl);
            $curl_error = curl_error($curl);
            $curl_errorNo = curl_errno($curl);
        }else{
            $info = null;
            $curl_error = null;
            $curl_errorNo = null;
        }
        curl_close($curl);
        return new Response($result,$info,$curl_error,$curl_errorNo);
    }
}