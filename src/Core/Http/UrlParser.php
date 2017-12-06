<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/5
 * Time: 下午2:36
 */

namespace EasySwoole\Core\Http;


class UrlParser
{
    public static function pathInfo($path)
    {
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