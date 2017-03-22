<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 14:51
 */

namespace Core\Utility\Validate;


class Verify
{
    protected $currentErrorMsg;
    protected $currentErrorRule;
    protected $currentErrorColumn;
    protected $currentErrorRawData;
    protected $finalResult;
    protected $dataForVerify;

    private $checkMethodMap = array(
        "is_num"=>"isNum",
        "isDecimal"=>"isDecimal",
        "required"=>"required",
        "max_num"=>"maxNum",
        "max_len"=>"maxLen",
        "min_num"=>"minNum",
        "min_len"=>"minLen",
        "ip"=>"isIp",
        "url"=>"isUrl",
        "email"=>"isEmail",
        "alpha"=>"isAlpha",
        "boolean"=>"isBoolean",
        "timestamp"=>"timestamp",
        "regex"=>"regex",
        "notEmpty"=>"notEmpty"
    );
    function __construct(array $data,Rules $rules)
    {
        $this->dataForVerify = $data;
        $ruleList = $rules->getRules();
        foreach ($ruleList as $key => $keyRules){
            //if keyRules is not empty
            if(!empty($keyRules)){
                foreach ($keyRules as $currentRules => $currentRulesParams){
                    $callMethod = $this->checkMethodMap[$currentRules];
                    if(empty($callMethod)){
                        trigger_error("verify rule {$currentRules} is not support");
                        return;
                    }
                    $flag = call_user_func_array(
                        array($this,$callMethod),
                        array($key,$currentRulesParams['params'])
                    );
                    if($flag == false){
                        $this->currentErrorColumn = $key;
                        $this->currentErrorMsg = $currentRulesParams['alertMsg'];
                        $this->currentErrorRule = $currentRules;
                        @$this->currentErrorRawData = $data[$key];
                        //once a rule check fail      free all
                        $this->finalResult = null;
                        return;
                    }else{
                        $this->finalResult[$key] = $this->getData($key);
                    }
                }
            }else{
                $this->finalResult[$key] = $this->getData($key);
            }
        }
    }
    function result(){
        return $this->finalResult;
    }
    function error(){
        return new Error($this->currentErrorColumn,$this->currentErrorRawData,$this->currentErrorRule,$this->currentErrorMsg);
    }
    private function getData($key){
        if(isset($this->dataForVerify[$key])){
            return $this->dataForVerify[$key];
        }else{
            return null;
        }
    }

    private function affectData($key,$data){
        $this->dataForVerify[$key] = $data;
    }
    /*
     * all  belows are the  method for check rules
     * and must return true or false,when each was call,it will
     * pass three param(key,$args),
     * remember ,if your want to change the value of a key,
     * please use class method : affectData
     */

    private function isNum($key,$args){
        $data = $this->getData($key);
        if(is_numeric($data)){
            return true;
        }else{
            return false;
        }
    }
    private function isDecimal($key,$args){
        $data = $this->getData($key);
        if(is_int($data)){
            return true;
        }else{
            return false;
        }
    }
    private function required($key,$args){
        $data = $this->getData($key);
        if(isset($data)){
            return true;
        }else{
            return false;
        }
    }
    private function maxLen($key,$args){
        $data = $this->getData($key);
        if(strlen($data) > $args){
            return false;
        }else{
            return true;
        }
    }
    private function minLen($key,$args){
        $data = $this->getData($key);
        if(strlen($data) < $args){
            return false;
        }else{
            return true;
        }
    }
    private function isEmail($key,$args){
        $data = $this->getData($key);
        $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if(preg_match($pattern, $data)){
            return true;
        }else{
            return false;
        }
    }
    private function isAlpha($key,$args){
        $data = $this->getData($key);
        if (preg_match('/^[a-zA-Z]+$/',$data)) {
            return true;
        }else{
            return false;
        }
    }
    private function isBoolean($key,$args){
        $data = $this->getData($key);
        if(($data == 0) || ($data == 1)){
            return true;
        }else{
            return false;
        }
    }
    private function isUrl($key,$args){
        $data = $this->getData($key);
        if (filter_var ($data, FILTER_VALIDATE_URL )) {
            return true;
        } else {
            return false;
        }
    }
    private function isIp($key,$args){
        $data = $this->getData($key);
        if (filter_var ($data, FILTER_VALIDATE_IP )) {
            return true;
        } else {
            return false;
        }
    }
    private function minNum($key,$args){
        $data = $this->getData($key);
        if(is_integer($data) && ($data > $args)){
            return true;
        }else{
            return false;
        }
    }
    private function maxNum($key,$args){
        $data = $this->getData($key);
        if(is_integer($data) && ($data < $args)){
            return true;
        }else{
            return false;
        }
    }
    private function timestamp($key,$args){
        $data = $this->getData($key);
        if(strtotime(date('d-m-Y H:i:s',$data)) === (int)$data) {
            return true;
        } else {
            return false;
        }
    }
    private function regex($key,$args){
        $data = $this->getData($key);
        $args = '/'.trim($args,'/').'/';
        if(preg_match($args,$data,$match)){
            return true;
        }else{
            return false;
        }
    }
    private function notEmpty($key,$args){
        $data = $this->getData($key);
        if(is_object($data)){
            if(empty($data)){
                return false;
            }else{
                return true;
            }
        }else{
            if(strlen($data) != 0){
                return true;
            }else{
                return false;
            }
        }
    }
}