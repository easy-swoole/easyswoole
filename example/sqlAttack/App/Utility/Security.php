<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/8/16
 * Time: 下午4:07
 */

namespace App\Utility;


class Security
{
    /*
     * 本注入脚本从网上流传的360 php防注入代码改版  仅供做参考
     */
    private $getFilter = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    private $postFilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    private $cookieFilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    function check(array $data){
        foreach ($data as $item){
            if (preg_match("/".$this->getFilter."/is",$item) == 1){
                return true;
            }
            if (preg_match("/".$this->postFilter."/is",$item) == 1){
                return true;
            }
            if (preg_match("/".$this->cookieFilter."/is",$item) == 1){
                return true;
            }
        }
        return false;
    }

}