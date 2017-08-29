<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/8/29
 * Time: 下午12:35
 */

namespace Core\Utility;


class Judge
{

    /*
     * 说明  本类的存在并非脱裤子放屁，仅仅为了防止新人出现 if(empty(0)){}
     * 甚至是出现if(md5("400035577431") == md5("mcfog_42r6i8"))的问题
     */


    static function isEqual($val,$val2){
        if($val == $val2){
            return true;
        }else{
            return false;
        }
    }
    static function isStrictEqual($val,$val2){
        if($val === $val2){
            return true;
        }else{
            return false;
        }
    }
    static function isNull($val){
        return is_null($val);
    }
    /*
     * 注意  0不为空，为解决  php内0为空问题
     */
    static function isEmpty($val){
        if($val === 0 || $val === '0'){
            return false;
        }else{
            return empty($val);
        }
    }
    /*
     * 接受  0，1 true，false
     */
    static function boolean($val,$strict = false){
        if($strict){
            return is_bool($val);
        }else{
            if(is_bool($val) || $val == 0 || $val == 1){
                return true;
            }else{
                return false;
            }

        }
    }
    static function isTrue($val,$strict = false){
        if($strict){
            if($val === true){
                return true;
            }else{
                return false;
            }
        }else{
            if($val == 1){
                return true;
            }else{
                return false;
            }
        }
    }
    static function isFalse($val,$strict = false){
        if($strict){
            if($val === false){
                return true;
            }else{
                return false;
            }
        }else{
            if($val == 0){
                return true;
            }else{
                return false;
            }
        }
    }
}