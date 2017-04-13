<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/1
 * Time: 下午2:52
 */

namespace Core\Component\Spl;


class SplString
{
    private $rawString;
    function __construct($rawString = null)
    {
        $this->rawString = (string)$rawString;
    }
    function setString($string){
        $this->rawString = (String)$string;
        return $this;
    }
    function split($length = 1){
        $this->rawString =  str_split($this->rawString,$length);
        return $this;
    }

    function encodingConvert($desEncoding,$detectList = array(
        'UTF-8',
        'ASCII',
        'GBK',
        'GB2312',
        'LATIN1',
        'BIG5',
        "UCS-2"
    )){
        $fileType = mb_detect_encoding ( $this->rawString,$detectList);
        if ($fileType != $desEncoding) {
            $this->rawString =  mb_convert_encoding ( $this->rawString, $desEncoding , $fileType );
            return $this;
        }else{
            return $this;
        }
    }

    function toUtf8(){
        return  $this->encodingConvert("UTF-8");
    }

    function toUnicode(){
        $raw = $this->encodingConvert("UCS-2");
        $len  = strlen($raw);
        $str  = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2){
            $c  = $raw[$i];
            $c2 = $raw[$i + 1];
            if (ord($c) > 0){   //两个字节的文字
                $str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
            } else {
                $str .= '\u'.str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
            }
        }
        $this->rawString =  strtoupper($str);//转换为大写
        return $this;
    }

    function explode($separator){
        return new SplArray(explode($separator,$this->rawString));
    }
    function subString($start,$length){
        $this->rawString =  substr($this->rawString,$start,$length);
        return $this;
    }
    function compare($str,$ignoreCase = 0){
        if($ignoreCase){
            return strcasecmp($str,$this->rawString);
        }else{
            return strcmp($str,$this->rawString);
        }
    }

    function lTrim($charList = null){
        $this->rawString =  ltrim($this->rawString,$charList);
        return $this;
    }
    function rTrim($charList = null){
        $this->rawString =  rtrim($this->rawString,$charList);
        return $this;
    }
    function trim($charList = null){
        $this->rawString =  trim($this->rawString,$charList);
        return $this;
    }

    function pad($length,$padString = null,$pad_type = STR_PAD_RIGHT ){
        $this->rawString =  str_pad($this->rawString,$length,$padString,$pad_type);
        return $this;
    }
    function repeat($times){
        $this->rawString =  str_repeat($this->rawString,$times);
        return $this;
    }

    function length(){
        return strlen($this->rawString);
    }

    function toUpper(){
        $this->rawString =  strtoupper($this->rawString);
        return $this;
    }
    function toLower(){
        $this->rawString =  strtolower($this->rawString);
        return $this;
    }
    function __toString()
    {
        // TODO: Implement __toString() method.
        return (String)$this->rawString;
    }
    function stripTags($allowable_tags = null){
        $this->rawString =  strip_tags($this->rawString,$allowable_tags);
        return $this;
    }
    function replace($find,$replaceTo){
        $this->rawString = str_replace($find,$replaceTo,$this->rawString);
        return $this;
    }

}