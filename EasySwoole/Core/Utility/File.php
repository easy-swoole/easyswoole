<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/2
 * Time: 下午10:50
 */

namespace EasySwoole\Core\Utility;


class File
{
    const TYPE_FILE = 1;
    const TYPE_DIR = 2;
    static function createDir($dirPath):bool
    {
        if(!is_dir($dirPath)){
            try{
                return mkdir($dirPath,0755,true);
            }catch (\Exception $exception){
                return false;
            }
        }else{
            return true;
        }
    }
    static function deleteDir($dirPath):bool
    {
        if(self::clearDir($dirPath)){
            try{
                return rmdir($dirPath);
            }catch (\Exception $exception){
                return false;
            }
        }else{
            return false;
        }
    }
    static function clearDir($dirPath):bool
    {
        if (!is_dir($dirPath)) {
            return false;
        }
        try{
            $dirHandle = opendir($dirPath);
            if(!$dirHandle){
                return false;
            }
            while (false !== ($file = readdir($dirHandle))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (!is_dir($dirPath ."/". $file)) {
                    if(!self::deleteFile($dirPath ."/". $file)){
                        closedir($dirHandle);
                        return false;
                    }
                } else {
                    if(!self::deleteDir($dirPath ."/". $file)){
                        closedir($dirHandle);
                        return false;
                    }
                }
            }
            closedir($dirHandle);
            return true;
        }catch (\Exception $exception){
            return false;
        }
    }
    static function copyDir($dirPath,$targetPath,$overwrite = true):bool
    {
        if (!is_dir($dirPath)) {
            return false;
        }
        if (!file_exists($targetPath)) {
            if(!self:: createDir($targetPath)){
                return false;
            }
        }
        try{
            $dirHandle = opendir($dirPath);
            if(!$dirHandle){
                return false;
            }
            while (false !== ($file = readdir($dirHandle))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (!is_dir($dirPath ."/". $file)) {
                    if(!self::copyFile($dirPath ."/". $file, $targetPath ."/". $file, $overwrite)){
                        closedir($dirHandle);
                        return false;
                    }
                } else {
                    if(!self::copyDir($dirPath ."/". $file, $targetPath ."/". $file, $overwrite)){
                        closedir($dirHandle);
                        return false;
                    };
                }
            }
            closedir($dirHandle);
            return true;
        }catch (\Exception $exception){
            return false;
        }
    }
    static function moveDir($dirPath,$targetPath,$overwrite = true):bool
    {
        try{
            if(self::copyDir($dirPath,$targetPath,$overwrite)){
                return self::deleteDir($dirPath);
            }else{
                return false;
            }
        }catch (\Exception $exception){
            return false;
        }
    }
    static function createFile($filePath, $overwrite = true):bool
    {
        if (file_exists($filePath) && $overwrite == false) {
            return false;
        } elseif (file_exists($filePath) && $overwrite == true) {
            if(!self::deleteFile($filePath)){
                return false;
            }
        }
        $aimDir = dirname($filePath);
        if(self::createDir($aimDir)){
            try{
                return touch($filePath);
            }catch (\Exception $exception){
                return false;
            }
        }else{
            return false;
        }
    }
    static function saveFile($filePath,$content, $overwrite = true):bool
    {
        if(self::createFile($filePath,$overwrite)){
            return (bool)file_put_contents($filePath,$content);
        }else{
            return false;
        }
    }
    static function copyFile($filePath,$targetFilePath,$overwrite = true):bool
    {
        if (!file_exists($filePath)) {
            return false;
        }
        if (file_exists($targetFilePath) && $overwrite == false) {
            return false;
        } elseif (file_exists($targetFilePath) && $overwrite == true) {
            if(!self::deleteFile($targetFilePath)){
                return false;
            }
        }
        $aimDir = dirname($filePath);
        if(!self::createDir($aimDir)){
            return false;
        };
        return copy($filePath, $targetFilePath);
    }
    static function moveFile($filePath,$targetFilePath,$overwrite = true):bool
    {
        if (!file_exists($filePath)) {
            return false;
        }
        if (file_exists($targetFilePath) && $overwrite == false) {
            return false;
        } elseif (file_exists($targetFilePath) && $overwrite == true) {
            if(!self::deleteFile($targetFilePath)){
                return false;
            }
        }
        $targetDir = dirname($targetFilePath);
        if(!self:: createDir($targetDir)){
            return false;
        }
        return rename($filePath, $targetFilePath);
    }

    static function deleteFile($filePath):bool
    {
        try{
            unlink($filePath);
            return true;
        }catch (\Exception $exception){
            return false;
        }
    }

    static function scanDir($dirPath, $filterType = self::TYPE_FILE):?array
    {
        $res = [];
        if(is_dir($dirPath)){
            try{
                $dirHandle = opendir($dirPath);
                if(!$dirHandle){
                    return null;
                }
                while (false !== ($file = readdir($dirHandle))) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    if($filterType == self::TYPE_FILE){
                        if (!is_dir($dirPath ."/". $file)) {
                            $res[] = $dirPath ."/". $file;
                        }
                    }else{
                        if (is_dir($dirPath ."/". $file)) {
                            $res[] = $dirPath ."/". $file;
                        }
                    }
                }
                closedir($dirHandle);
                return $res;
            }catch (\Exception $exception){
                return null;
            }
        }else{
            return null;
        }
    }
}