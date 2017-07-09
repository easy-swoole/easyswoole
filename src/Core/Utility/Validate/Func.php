<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/25
 * Time: 下午2:48
 */

namespace Core\Utility\Validate;


use Core\Component\Spl\SplArray;

class Func
{
    static function ACTIVE_URL($column,SplArray $array,array $args){
        $data = $array->get($column);
        if(!empty($data)){
            return checkdnsrr(parse_url($data,PHP_URL_HOST));
        }else{
            return false;
        }
    }
    static function ALPHA($column,SplArray $array,array $args){
        $data = $array->get($column);
        if(preg_match('/^[a-zA-Z]+$/',$data)){
            return true;
        }else{
            return false;
        }
    }
    static function BETWEEN($column,SplArray $array,array $args){
        $data = $array->get($column);
        if(is_numeric($data) || is_string($data)){
            $min = array_shift($args);
            $max = array_shift($args);
            if($data <= $max && $data >= $min){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    static function BOOLEAN($column,SplArray $array,array $args){
        $data = $array->get($column);
        if(($data == 1) || ($data == 0)){
            return true;
        }else{
            return false;
        }
    }
    static function DATE($column,SplArray $array,array $args){
        $data = $array->get($column);
        $format = array_shift($args);
        $format = $format ?: 'Y-m-d H:i:s';
        $unixTime  =  strtotime($data);
        $checkDate = date($format, $unixTime);
        if($checkDate == $data){
            return true;
        }
        else{
            return false;
        }
    }
    static function DATE_AFTER($column,SplArray $array,array $args){
        $data = $array->get($column);
        $unixTime  =  strtotime($data);
        $after = array_shift($args);
        $afterUnixTime = empty($after) ? strtotime($after) : time();
        if($unixTime > $afterUnixTime){
            return true;
        }else{
            return false;
        }
    }
    static function DATE_BEFORE($column,SplArray $array,array $args){
        $data = $array->get($column);
        $unixTime  =  strtotime($data);
        $after = array_shift($args);
        $afterUnixTime = empty($after) ? strtotime($after) : time();
        if($unixTime > $afterUnixTime){
            return false;
        }else{
            return true;
        }
    }
    static function DIFFERENT($column,SplArray $array,array $args){
        $data = $array->get($column);
        foreach ($args as $diffCol){
            if($data === $array->get($diffCol)){
                return false;
            }
        }
        return true;
    }
    static function FLOAT($column,SplArray $array,array $args){
        $data = $array->get($column);
        return is_float($data);
    }
    static function IN($column,SplArray $array,array $args){
        $data = $array->get($column);
        return in_array($data,$args);
    }
    static function INTEGER($column,SplArray $array,array $args){
        $data = $array->get($column);
        return filter_var($data, FILTER_VALIDATE_INT);
    }
    static function IP($column,SplArray $array,array $args){

    }
    static function ARRAY_($column,SplArray $array,array $args){
        $data = $array->get($column);
        return is_array($data);
    }
    static function LEN($column,SplArray $array,array $args){
        $data = $array->get($column);
        if(is_numeric($data) || is_string($data)){
            $len = array_shift($args);
            if(strlen($data) == $len){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    static function NOT_IN($column,SplArray $array,array $args){
        $data = $array->get($column);
        return !in_array($data,$args);
    }
    static function NUMERIC($column,SplArray $array,array $args){
        $data = $array->get($column);
        return is_numeric($data);
    }
    static function MAX($column,SplArray $array,array $args){
        $data = $array->get($column);
        if(is_numeric($data) || is_string($data)){
            $com = array_shift($args);
            if($data <= $com){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    static function MAX_LEN($column,SplArray $array,array $args){
        $data = $array->get($column);
        if(is_numeric($data) || is_string($data)){
            $com = array_shift($args);
            if(strlen($data) <= $com){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    static function MIN($column,SplArray $array,array $args){
        $data = $array->get($column);
        if(is_numeric($data) || is_string($data)){
            $com = array_shift($args);
            if($data >= $com){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    static function MIN_LEN($column,SplArray $array,array $args){
        $data = $array->get($column);
        if(is_numeric($data) || is_string($data)){
            $com = array_shift($args);
            if(strlen($data) >= $com){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    static function REGEX($column,SplArray $array,array $args){
        $regex = array_shift($args);
        $data = $array->get($column);
        if(preg_match($regex,$data)){
            return true;
        }else{
            return false;
        }
    }
    static function REQUIRED($column,SplArray $array,array $args){
        $data = $array->get($column);
        return $data === null ? false : true;
    }
    static function REQUIRED_IF($column,SplArray $array,array $args){
        $requiredCol = array_shift($args);
        $ifVal = $array->get($requiredCol);
        if(in_array($ifVal,$args)){
            return self::REQUIRED($column,$array,array());
        }else{
            return true;
        }
    }
    static function REQUIRE_WITH($column,SplArray $array,array $args){
        foreach ($args as $col){
            if(self::REQUIRED($col,$array,array())){
                return self::REQUIRED($column,$array,array());
            }
        }
        return true;
    }
    static function REQUIRE_WITH_ALL($column,SplArray $array,array $args){
        $flag = true;
        foreach ($args as $col){
            if(!self::REQUIRED($col,$array,array())){
                $flag = false;
                break;
            }
        }
        if($flag){
            return self::REQUIRED($column,$array,array());
        }else{
            return true;
        }
    }
    static function REQUIRE_WITHOUT($column,SplArray $array,array $args){
        foreach ($args as $col){
            if(!self::REQUIRED($col,$array,array())){
                return self::REQUIRED($column,$array,array());
            }
        }
        return true;
    }
    static function REQUIRE_WITHOUT_ALL($column,SplArray $array,array $args){
        $flag = false;
        foreach ($args as $col){
            if(self::REQUIRED($col,$array,array())){
                $flag = true;
                break;
            }
        }
        if($flag){
            return true;
        }else{
            return self::REQUIRED($column,$array,array());
        }
    }
    static function SAME($column,SplArray $array,array $args){
        $data = $array->get($column);
        foreach ($args as $diffCol){
            if($data !== $array->get($diffCol)){
                return false;
            }
        }
        return true;
    }
    static function TIMESTAMP($column,SplArray $array,array $args){
        $data = $array->get($column);
        if(strtotime(date("d-m-Y H:i:s",$data)) === (int)$data){
            return true;
        }else{
            return false;
        }
    }
    static function URL($column,SplArray $array,array $args){
        $data = $array->get($column);
        return filter_var($data, FILTER_VALIDATE_URL);
    }

    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
        trigger_error("validate rule {$name} not support");
        return false;
    }
}