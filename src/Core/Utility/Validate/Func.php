<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/3
 * Time: 下午1:58
 */

namespace EasySwoole\Core\Utility\Validate;


use EasySwoole\Core\Component\Spl\SplArray;

class Func
{
    static function ACTIVE_URL($data,array $rawData,$args):bool
    {
        if(is_string($data)){
            return checkdnsrr(parse_url($data,PHP_URL_HOST));
        }else{
            return false;
        }
    }

    static function ALPHA($data,array $rawData,$args):bool
    {
        if(is_string($data)){
            return preg_match('/^[a-zA-Z]+$/',$data);
        }else{
            return false;
        }
    }

    static function BETWEEN($data,array $rawData,$args):bool
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

    static function BOOLEAN($data,array $rawData,$args):bool
    {
        if(($data == 1) || ($data == 0)){
            return true;
        }else{
            return false;
        }
    }

    static function DATE($data,array $rawData,$args):bool
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

    static function DATE_AFTER($data,array $rawData,$args):bool
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

    static function DATE_BEFORE($data,array $rawData,$args):bool
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

    static function DIFFERENT($data,array $rawData,$args):bool
    {
        $spl = new SplArray($rawData);
        foreach ($args as $col){
            if($data === $spl->get($col)){
                return false;
            }
        }
        return true;
    }

    static function EMPTY($data,array $rawData,$args):bool
    {
        if($data === 0 || $data === '0'){
            return false;
        }else{
            return empty($data);
        }
    }

    static function FLOAT($data,array $rawData,$args):bool
    {
        return filter_var($data,FILTER_VALIDATE_FLOAT);
    }

    static function IN($data,array $rawData,$args):bool
    {
        return in_array($data,$args);
    }

    static function INTEGER($data,array $rawData,$args):bool
    {
        return filter_var($data, FILTER_VALIDATE_INT);
    }

    static function IP($data,array $rawData,$args):bool
    {
        return filter_var($data, FILTER_VALIDATE_IP);
    }

    static function IS_ARRAY($data,array $rawData,$args):bool
    {
        return is_array($data);
    }

    static function LEN($data,array $rawData,$args):bool
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

    static function NOT_IN($data,array $rawData,$args):bool
    {
        return !in_array($data,$args);
    }

    static function NUMERIC($data,array $rawData,$args):bool
    {
        return is_numeric($data);
    }

    static function MAX($data,array $rawData,$args):bool
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

    static function MAX_LEN($data,array $rawData,$args):bool
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

    static function MIN($data,array $rawData,$args):bool
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

    static function MIN_LEN($data,array $rawData,$args):bool
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

    public static function OPTIONAL($data,array $rawData,$args){
        return true;
    }

    static function REGEX($data,array $rawData,$args):bool
    {
        $regex = array_shift($args);
        if(is_numeric($data) || is_string($data)){
            return preg_match($regex,$data);
        }else{
            return false;
        }
    }

    static function REQUIRED($data,array $rawData,$args):bool {
        return $data === null ? false : true;
    }

    static function SAME($data,array $rawData,$args):bool
    {
        $spl = new SplArray($rawData);
        foreach ($args as $col){
            if($data !== $spl->get($col)){
                return false;
            }
        }
        return true;
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

    static function URL($data,array $rawData,$args):bool
    {
        return filter_var($data,FILTER_VALIDATE_URL);
    }



    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
        trigger_error("validate rule {$name} not support");
        return false;
    }
}