<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 下午3:59
 */

namespace Core\Utility\Curl;


class Utility
{
    /**
     * @param $resource
     * @param bool $toString
     * @return array|string|bool
     */
    public static function getCookieFromHeader($resource, $toString = false){
        preg_match_all("/Set-Cookie:(.*)\n/U",$resource,$ret);
        if(!empty($ret[0])){
            $all = array();
            $stringRet = '';
            foreach ($ret[0] as $item){
                $temp = explode(";",$item);
                $cookie = ltrim($temp[0],"Set-Cookie:");
                $temp2 = explode("=",$cookie);
                $cookieName = trim($temp2[0]);
                $cookieValue = trim($temp2[1]);
                /*
                 * when cookie is set as a session
                 * expires path domain might be empty
                 */
                @$expires = explode("=",$temp[1]);
                @$expires = trim($expires[1]);
                @$path = explode("=",$temp[2]);
                @$path = trim($path[1]);
                @$domain = explode('=',$temp[3]);
                @$domain = trim($domain[1]);
                array_push($all,array(
                    "name"=>$cookieName,
                    "value"=>$cookieValue,
                    "expires"=>strtotime($expires),
                    "path"=>$path,
                    'domain'=>$domain
                ));
                $stringRet .= "{$cookieName}={$cookieValue}; ";
            }
            if($toString){
                return $stringRet;
            }else{
                return $all;
            }
        }else{
            return false;
        }
    }
    /**
     * @param $path
     * @param bool $toString
     * @return array|string|bool
     */
    public static function getCookieFromCurlFile($path,$toString = false){
        if($lines = file($path)){
            $ret = array();
            $stringRet = '';
            foreach($lines as $line) {
                if($line[0] != '#' && substr_count($line, "\t") == 6) {
                    $cookie = explode("\t", $line);
                    $cookie = array_map('trim', $cookie);
                    $ret[] = array(
                        "key"=>$cookie[5],
                        "value"=>$cookie[6]
                    );
                    $stringRet .= "{$cookie[5]}={$cookie[6]}; ";
                }
            }
            if($toString){
                return $stringRet;
            }else{
                return $ret;
            }
        }else{
            return false;
        }
    }
    public static function optForPostFileFormMemory($postFields,$fileFields){
        // form field separator
        // file upload fields: name => array(type=>'mime/type',content=>'raw data')
        //        $fileFields = array(
        //            'field' => array(
        //                'type' => 'text/plain',
        //                'content' => '...your raw file content goes here...'
        //            ),
        //        );
        // all other fields (not file upload): name => value
        //      $postFields = array(
        //            'otherformfield'   => 'content of otherformfield is this text',
        //        );
        //构造post数据
        $data = '';
        $delimiter = '-------------' . uniqid();
        // 表单数据
        foreach ($postFields as $name => $content) {
            $data .= "--" . $delimiter . "\r\n";
            $data .= 'Content-Disposition: form-data; name="' . $name . '"';
            $data .= "\r\n\r\n";
            $data .= $content;
            $data .= "\r\n";
        }
        foreach ($fileFields as $inputName => $file) {
            $data .= "--" . $delimiter . "\r\n";
            $data .= 'Content-Disposition: form-data; name="' . $inputName . '";' .
                ' filename="' . $file['filename'] . '"' . "\r\n";
            $data .= 'Content-Type: ' . $file['type'] . "\r\n";
            $data .= "\r\n";
            $data .= $file['content'] . "\r\n";
        }
        //结束符
        $data .= "--" . $delimiter . "--\r\n";
        return array(
            CURLOPT_HTTPHEADER=>array(
                'Content-Type:multipart/form-data;boundary=' . $delimiter,
                'Content-Length:' . strlen($data)
            ),
            CURLOPT_POST=>true,
            CURLOPT_POSTFIELDS=>$data

        );
    }
    public static function trimHeader($resource){
        return substr($resource, strpos($resource, "\r\n\r\n") + 4);
    }
    public static function getHeader($resource){
        return substr($resource,0, strpos($resource, "\r\n\r\n") + 4);
    }
}