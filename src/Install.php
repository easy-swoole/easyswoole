<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/1
 * Time: 下午7:46
 */

namespace EasySwoole\Frame;


class Install
{
    public static function extraFile()
    {
        Core::getInstance();
        //强制更新easyswoole bin管理文件
        if(is_file(EASYSWOOLE_ROOT . '/easyswoole')){
            unlink(EASYSWOOLE_ROOT . '/easyswoole');
        }
        copy(__DIR__.'/../bin/easyswoole.php',EASYSWOOLE_ROOT . '/easyswoole');
        self::releaseResource(__DIR__ . '/Resource/Config.tpl', EASYSWOOLE_ROOT . '/Config.php');
        self::releaseResource(__DIR__ . '/Resource/EasySwooleEvent.tpl', EASYSWOOLE_ROOT . '/EasySwooleEvent.php');
    }

    protected static function releaseResource($source, $destination)
    {
        clearstatcache();
        $replace = true;
        if (is_file($destination)) {
            $filename = basename($destination);
            echo "{$filename} has already existed, do you want to replace it? [ Y / N (default) ] : ";
            $answer = strtolower(trim(strtoupper(fgets(STDIN))));
            if (!in_array($answer, [ 'y', 'yes' ])) {
                $replace = false;
            }
        }
        if ($replace) {
            copy($source, $destination);
        }
    }
}