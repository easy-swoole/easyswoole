<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/3
 * Time: 下午1:58
 */

namespace EasySwoole\Core\Utility\Validate;


class Func
{
    static function ACTIVE_URL($data,$args):bool
    {
        if(is_string($data)){
            return checkdnsrr(parse_url($data,PHP_URL_HOST));
        }else{
            return false;
        }
    }

    static function ALPHA($data,$args):bool
    {
        if(is_string($data)){
            return preg_match('/^[a-zA-Z]+$/',$data);
        }else{
            return false;
        }
    }

    static function BETWEEN($data,$args):bool
    {
        $min = array_shift($args);
        $max = array_shift($args);
        if(is_numeric($data) || is_string($data)){
            if($data <= $max && $data >= $min){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    static function BOOLEAN($data,$args):bool
    {
        if(($data == 1) || ($data == 0)){
            return true;
        }else{
            return false;
        }
    }

    static function DATE($data,$args):bool
    {
        $format = array_shift($args) ?: 'Y-m-d H:i:s';
        if(is_string($data)){
            $unixTime  =  strtotime($data);
            $checkDate = date($format, $unixTime);
            if($checkDate == $data){
                return true;
            }
            else{
                return false;
            }
        }else{
            return false;
        }
    }

    static function DATE_AFTER($data,$args):bool
    {
        $after = array_shift($args);
        $afterUnixTime = empty($after) ? strtotime($after) : time();
        if(is_string($data)){
            $unixTime  =  strtotime($data);
            if($unixTime > $afterUnixTime){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    static function DATE_BEFORE($data,$args):bool
    {
        $before = array_shift($args);
        $beforeUnixTime = empty($after) ? strtotime($before) : time();
        if(is_string($data)){
            $unixTime  =  strtotime($data);
            if($unixTime < $beforeUnixTime){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    static function FLOAT($data,$args):bool
    {
        return filter_var($data,FILTER_VALIDATE_FLOAT);
    }

    static function IN($data,$args):bool
    {
        return in_array($data,$args);
    }

    static function INTEGER($data,$args):bool
    {
        return filter_var($data, FILTER_VALIDATE_INT);
    }

    static function IP($data,$args):bool
    {
        return filter_var($data, FILTER_VALIDATE_IP);
    }

    static function IS_ARRAY($data,$args):bool
    {
        return is_array($data);
    }

    static function LEN($data,$args):bool
    {
        $len = array_shift($args);
        if(is_numeric($data) || is_string($data)){
            if(strlen($data) == $len){
                return true;
            }else{
                return false;
            }
        }else if(is_array($data)){
            if(count($data) == $len){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    static function NOT_IN($data,$args):bool
    {
        return !in_array($data,$args);
    }

    static function NUMERIC($data,$args):bool
    {
        return is_numeric($data);
    }

    static function MAX($data,$args):bool
    {
        $com = array_shift($args);
        if(is_numeric($data) || is_string($data)){
            if($data <= $com){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    static function MAX_LEN($data,$args):bool
    {
        $len = array_shift($args);
        if(is_numeric($data) || is_string($data)){
            if(strlen($data) <= $len){
                return true;
            }else{
                return false;
            }
        }else if(is_array($data)){
            if(count($data) <= $len){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    static function MIN($data,$args):bool
    {
        $com = array_shift($args);
        if(is_numeric($data) || is_string($data)){
            if($data >= $com){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    static function MIN_LEN($data,$args):bool
    {
        $len = array_shift($args);
        if(is_numeric($data) || is_string($data)){
            if(strlen($data) >= $len){
                return true;
            }else{
                return false;
            }
        }else if(is_array($data)){
            if(count($data) >= $len){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    static function REGEX($data,$args):bool
    {
        $regex = array_shift($args);
        if(is_numeric($data) || is_string($data)){
            return preg_match($regex,$data);
        }else{
            return false;
        }
    }

    static function REQUIRED($data,$args):bool {
        return $data === null ? false : true;
    }

    static function TIMESTAMP($data,$args):bool
    {
        if(is_numeric($data)){
            if(strtotime(date("d-m-Y H:i:s",$data)) === (int)$data){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    static function URL($data,$args):bool
    {
        return filter_var($data,FILTER_VALIDATE_URL);
    }

    public static function OPTIONAL($data,$args){
        return true;
    }

    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
        trigger_error("validate rule {$name} not support");
        return false;
    }
}