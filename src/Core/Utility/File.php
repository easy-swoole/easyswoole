<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/7
 * Time: 下午12:15
 */

namespace Core\Utility;


class File
{
    static function createDir($dirPath){
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
    static function deleteDir($dirPath){
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
    static function clearDir($dirPath){
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
    static function copyDir($dirPath,$targetPath,$overwrite = true){
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
    static function moveDir($dirPath,$targetPath,$overwrite = true){
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
    static function createFile($filePath, $overwrite = true){
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
    static function saveFile($filePath,$content, $overwrite = true){
        if(self::createFile($filePath,$overwrite)){
            return file_put_contents($filePath,$content);
        }else{
            return false;
        }
    }
    static function copyFile($filePath,$targetFilePath,$overwrite = true){
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
    static function moveFile($filePath,$targetFilePath,$overwrite = true){
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
    static function deleteFile($filePath){
        try{
            unlink($filePath);
            return true;
        }catch (\Exception $exception){
            return false;
        }
    }
}