<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:46
 */

namespace Core;


use Core\Component\Logger;
use Core\Http\Request;

class UrlParser
{
    static public function pathInfo($path = null){
        if($path == null){
            $path = Request::getInstance()->getUri()->getPath();
        }
        $basePath = dirname($path);
        $info = pathInfo($path);
        if($info['filename'] != 'index'){
            if($basePath == '/'){
                $basePath = $basePath.$info['filename'];
            }else{
                $basePath = $basePath.'/'.$info['filename'];
            }
        }
        return $basePath;
    }
}