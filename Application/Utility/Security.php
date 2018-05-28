<?php
namespace App\Utility;
use voku\helper\AntiXSS;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13
 * Time: 18:35
 */
class Security
{
    /*
     * 本注入脚本从网上流传的360 php防注入代码改版  仅供做参考
     */
    private static $getFilter = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    private static $postFilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    private static $cookieFilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

    /**
     * @param array $data
     * @return bool 返回true 表示校验通过, false则表示不安全
     */
     static function check(array $data){
        foreach ($data as $item){
            if (preg_match("/".Security::$getFilter."/is",$item) == 1){
                return false;
            }
            if (preg_match("/".Security::$postFilter."/is",$item) == 1){
                return false;
            }
            if (preg_match("/".Security::$cookieFilter."/is",$item) == 1){
                return false;
            }
        }
        return true;
    }

    /**
     * xss过滤
     * @param string $str
     */
    static function removeXss(string &$str){
        $antiXss = new AntiXSS();
        $str = $antiXss->xss_clean($str);
        unset($antiXss);
    }


    /**
     * xss 数组过滤
     * @param array $data
     */
    static function batchXssRemove(array &$data) {
        foreach ($data as &$v){
            self::removeXss($v);
        }
    }

}