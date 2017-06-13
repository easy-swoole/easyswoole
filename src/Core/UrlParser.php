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
    static public function pathInfo(){
        $pathInfo = Request\Request::getInstance()->getServer('PATH_INFO');
        $basePath = dirname($pathInfo);
        $info = pathInfo($pathInfo);
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