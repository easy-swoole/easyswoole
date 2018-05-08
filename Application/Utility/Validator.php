<?php
/**
 * Created by PhpStorm.
 * User: 111
 * Date: 2018/4/4
 * Time: 12:21
 */

namespace App\Utility;

//校验类
class Validator
{
    /**
     * 校验结果
     * @var null|array
     */
    private $validateRes = null;

    public function __construct(){
    }

    /**
     * 手机号校验
     * @param array $data 数组数据
     * @param string $field 需要校验的字段
     * @return bool
     */
    public function mobile(array &$data, string &$field) :bool {
        if(isset($data[$field])){
            return preg_match("/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\\d{8}$/", $data[$field]);
        }
        return true; //如果没有这个字段则不校验, 因为判断是否存在这个字段通过required决定
    }

    /**
     * 密码校验
     * 密码8-16位数字和字母的组合加上!?这两个符号(不能是纯数字或者纯字母)
     * @param array $data 数组数据
     * @param string $field 需要校验的字段
     * @return bool
     */
    public function password(array &$data, string &$field) :bool {
        if(isset($data[$field])) {
            return preg_match("/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/", $data[$field]);
        }
        return true;
    }

    /**
     * 用户昵称校验
     * 中文和英文或数字不能有特殊符号长度为2-10位
     * @param array $data 数组数据
     * @param string $field 需要校验的字段
     * @return bool
     */
    public function nick(array &$data, string &$field) :bool {
        if(isset($data[$field])) {
            return preg_match("/^[a-zA-Z0-9\x{4e00}-\x{9fa5}]{2,10}$/u", $data[$field]);
        }
        return true;
    }

    /**
     * 字段必传, 可以传控制付出,但是key里必须有
     * @param array $data 数组数据
     * @param string $field 需要校验的字段
     * @return bool
     */
    public function required(array &$data, string &$field) :bool{
        if (isset($data[$field])){
            return true;
        }
        return false;
    }

    /**
     * 字段非空
     * @param array $data 数组数据
     * @param string $field 需要校验的字段
     * @return bool
     */
    public function notEmpty(array &$data, string &$field) :bool{
        if (isset($data[$field]) && !empty($data[$field])){
            return true;
        }
        return false;
    }

    /**
     * @param array $data
     * @param string $field
     * @param float $min
     * @return bool
     */
    public function min(array &$data, string &$field, float $min) :bool{
        if(isset($data[$field])){
            return $data[$field] >= $min;
        }
        return true;
    }

    /**
     * @param array $data
     * @param string $field
     * @param float $max
     * @return bool
     */
    public function max(array &$data, string &$field, float $max) :bool{
        if(isset($data[$field])){
            return $data[$field] <= $max;
        }
        return true;
    }

    /**
     * 参数最大长度
     * @param array $data
     * @param string $field
     * @param int $maxLength
     * @return bool
     */
    public function maxLength(array &$data, string &$field, int $maxLength) :bool{
        if(isset($data[$field])){
            return mb_strlen($data[$field], 'utf-8') <= $maxLength;
        }
        return true;
    }

    /**
     * 参数最大长度
     * @param array $data
     * @param string $field
     * @param int $minLength
     * @return bool
     */
    public function minLength(array &$data, string &$field, int $minLength) :bool{
        if(isset($data[$field])){
            return mb_strlen($data[$field], 'utf-8') >= $minLength;
        }
        return true;
    }

    /**
     * 数值型
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function numeric(array &$data, string &$field) :bool{
        if(isset($data[$field])){
            return is_numeric($data[$field]);
        }
        return true;
    }

    /**
     * 是否为整数判断, 0也是整数
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function int(array &$data, string &$field) :bool{
        if(isset($data[$field])){
            $res = filter_var($data[$field], FILTER_VALIDATE_INT);
            if($res !== false){
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * 是否为json字符串
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function json(array &$data, string &$field) :bool{
        if(isset($data[$field])){
            if(empty(json_decode($data[$field], true))){
                return false;
            }
        }
        return true;
    }

    /**
     * 邮箱校验
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function email(array &$data, string &$field) :bool
    {
        if (isset($data[$field])) {
            $res = filter_var($data[$field], FILTER_VALIDATE_EMAIL);
            if($res != false){
                return true;
            }
            return false;
//            return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $data[$field]);
        }
        return true;
    }

    /**
     * 值范围校验, 比如有个需求, 客户端传入的某个字段参数必须在foo,good,park这三个值内, 如果不是则校验不过
     * @param array $data
     * @param string $field
     * @param string $args
     * @return bool
     */
    public function in(array &$data, string &$field, string $args) :bool{
        $argsArr = @explode(",", $args);
        $res = false;
        if (isset($data[$field])) {
            foreach ($argsArr as $v){
                if($v === $data[$field]){
                    $res = true;
                    break;
                }
            }
            return $res;
        }
        return true;
    }

    /**
     * 验证url
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function url(array &$data, string &$field) :bool {
        if(isset($data[$field])){
            $res = filter_var($data[$field], FILTER_VALIDATE_URL);
            if($res != false){
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * 正则校验
     * @param array $data
     * @param string $field
     * @param string $regex
     * @return bool
     */
    public function regex(array &$data, string &$field, string $regex) :bool{
        if(isset($data[$field])){
            return preg_match($regex, $data[$field]);
        }
        return true;
    }

    /**
     * @param array $data, 需要进行校验的数组
     * @param array $fieldsRules 校验规则数组,参数用点和|区分 如 array(
     *  "input1" => "numeric|min:10|in:x,y,z|",
     *  "input2" => "numeric|min:10|",
     * )
     * @param array $msg, 传对应字段校验出错后返回的错误信息, 如果没有, 则默认返回校验失败
     * @return bool
     */
    public function validate(array &$data, array $fieldsRules=array(), array $msg=array()) :bool {
        //遍历所有字段的校验规则, 针对每个字段的校验规则进行校验
        foreach ($fieldsRules as $field => $singleRules){
            $ruleArr = explode('|', $singleRules);
            $res = true; //单个校验结果, 默认是通过true
            foreach ($ruleArr as $rule){
                //如果规则为空, 则直接跳过
                if(empty($rule)){
                    continue;
                }
                $ruleContent = explode(':', $rule);
                $method = $ruleContent[0];
                $ruleContentLength = count($ruleContent);
                //根据参数长度, 使用校验, TODO 单个校验目前最多只有1个外界参数, 不包含固定的data和field
                switch ($ruleContentLength) {
                    case 1 :
                        $res = $this->$method($data, $field) && $res;
                        break;
                    case 2 :
                        $res = $this->$method($data, $field, $ruleContent[1]) && $res;
                        break;
                    default :
                        break;
                }
            }
            //如果单个字段校验没通过, 则将信息加入校验数组中
            if($res === false){
                $this->validateRes[$field] = isset($msg[$field]) ? $msg[$field] : "校验失败";
            }
        }
        return !$this->hasError();
    }

    /**
     * 是否校验通过
     * @return bool
     */
    public function hasError(){
       if(!empty($this->validateRes)){
           return true;
       }
       return false;
    }

    /**
     * 校验错误信息
     * @return array|null
     */
    public function errorList(){
        return $this->validateRes;
    }

}