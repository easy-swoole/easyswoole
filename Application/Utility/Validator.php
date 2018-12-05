<?php
/**
 * Created by PhpStorm.
 * User: azerothyang
 * Date: 2018/10/11
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

    /**
     * 自定义字段名称的msg
     * @var array
     */
    private $msg = array();

    public function __construct(){
    }

    /**
     * 校验日期型
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function date(array &$data, string &$field) :bool{
        if(isset($data[$field])) {
            return (bool)strtotime($data[$field]);
        }
        return true;
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
     * 逗号间隔电话校验如:   312312-31-413,14123-413,1846113
     * @param array $data 数组数据
     * @param string $field 需要校验的字段
     * @return bool
     */
    public function csPhone(array &$data, string &$field) :bool {
        if(isset($data[$field])){
            $data[$field] = str_replace("，", ",", $data[$field]); //将汉字逗号转为英文逗号保存
            $phones = explode(",", $data[$field]);
            foreach ($phones as $phone) {
                if(!preg_match("/^[0-9-]+$/", (string)$phone)) {
                    return false;
                }
                $eachPhones = explode("-", $phone);
                foreach ($eachPhones as $phoneNum) {
                    //如果不是数字, 则校验失败
                    if(!preg_match("/^[0-9]+$/", (string)$phoneNum)) {
                        return false;
                    }
                }
            }
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
        if (isset($data[$field])){
            if (!empty($data[$field])) {
                return true;
            }
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
     * 值范围校验, 比如有个需求, 客户端传入的某个字段参数必须在foo,good,park这三个值内, 如果不是则校验不过
     * @param array $data
     * @param string $field
     * @param string $args
     * @return bool
     */
    public function notIn(array &$data, string &$field, string $args) :bool{
        $argsArr = @explode(",", $args);
        $res = true;
        if (isset($data[$field])) {
            foreach ($argsArr as $v){
                if($v === $data[$field]){
                    $res = false;
                    break;
                }
            }
        }
        return $res;
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
     * string过滤, 去除html标签
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function string(array &$data, string &$field) :bool {
        if(isset($data[$field])){
            $data[$field] = strip_tags(strval($data[$field]));
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
     * 校验是否为json数组
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function jsonArray(array &$data, string &$field) :bool {
        if(isset($data[$field])){
            $content = @json_decode($data[$field], true);
            if (!empty($content) && is_array($content)) {
                return true;
            }
        }
        return false;
    }

    /**
     * json 数组参数校验, 目前支持到二维数组,一般伴随jsonArray一起使用
     * @param array $data
     * @param string $field
     * @param string $rules
     * @return bool
     */
    public function jsonArrayParams(array &$data, string $field, string $rules) :bool {
        //如果不包含*表示规则格式错误,开发人员问题
        if (strpos($field, "*") === false) {
            return false;
        }
        $fields = explode(".", $field);
        $field = $fields[0];
        $subField = $fields[2];
        if(isset($data[$field])){
            $contents = @json_decode($data[$field], true);
            if (!empty($contents) && is_array($contents)) {
                $res = true; //校验结果, 默认是通过true
                $rules = explode("@", $rules);
                foreach ($rules as $rule) {
                    //如果规则为空, 则直接跳过
                    if(empty($rule)){
                        continue;
                    }
                    $ruleContent = explode('=', $rule);
                    $method = $ruleContent[0];
                    $ruleContentLength = count($ruleContent);
                    //根据参数长度, 使用校验, TODO 单个校验目前最多只有1个外界参数, 不包含固定的data和field
                    switch ($ruleContentLength) {
                        case 1 :
                            foreach ($contents as $content) {
                                $res = $this->$method($content, $subField) && $res;
                                if ($res === false) {
                                    return $res;
                                }
                            }
                            break;
                        case 2 :
                            foreach ($contents as $content) {
                                $res = $this->$method($content, $subField, $ruleContent[1]) && $res;
                                if ($res === false) {
                                    return $res;
                                }
                            }
                            break;
                        default :
                            break;
                    }
                }
                return $res;
            }
        }
        return false;
    }

    /**
     * 校验是否为数组
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function array(array &$data, string &$field) :bool {
        if(isset($data[$field])){
            $res = is_array($data[$field]);
            return $res;
        }
        return true;
    }

    /**
     * 二维数组校验，内层数组所含的参数
     * @param array $data
     * @param string $field
     * @param string $rules
     * @return bool
     */
    public function arrayParams(array &$data, string $field, string $rules) :bool {
        //如果不包含*表示规则格式错误,开发人员问题
        if (strpos($field, "*") === false) {
            return false;
        }
        $fields = explode(".", $field);
        $field = $fields[0];
        $subField = $fields[2];
        if(isset($data[$field])){
            $contents = $data[$field];
            if (!empty($contents) && is_array($contents)) {
                $res = true; //校验结果, 默认是通过true
                $rules = explode("@", $rules);
                foreach ($rules as $rule) {
                    //如果规则为空, 则直接跳过
                    if(empty($rule)){
                        continue;
                    }
                    $ruleContent = explode('=', $rule);
                    $method = $ruleContent[0];
                    $ruleContentLength = count($ruleContent);
                    //根据参数长度, 使用校验, TODO 单个校验目前最多只有1个外界参数, 不包含固定的data和field
                    switch ($ruleContentLength) {
                        case 1 :
                            foreach ($contents as $content) {
                                $res = $this->$method($content, $subField) && $res;
                                if ($res === false) {
                                    return $res;
                                }
                            }
                            break;
                        case 2 :
                            foreach ($contents as $content) {
                                $res = $this->$method($content, $subField, $ruleContent[1]) && $res;
                                if ($res === false) {
                                    return $res;
                                }
                            }
                            break;
                        default :
                            break;
                    }
                }
                return $res;
            }
        }
        return false;
    }

    /**
     * 一维数组校验, 内部参数
     * @param array $data
     * @param string $field
     * @param string $rules
     * @return bool
     */
    public function arrayParam(array &$data, string $field, string $rules) :bool {
        $fields = explode(".", $field);
        $field = $fields[0];
        $subField = $fields[1];
        if(isset($data[$field])){
            $content = $data[$field];
            if (!empty($content) && is_array($content)) {
                $res = true; //校验结果, 默认是通过true
                $rules = explode("@", $rules);
                foreach ($rules as $rule) {
                    //如果规则为空, 则直接跳过
                    if(empty($rule)){
                        continue;
                    }
                    $ruleContent = explode('=', $rule);
                    $method = $ruleContent[0];
                    $ruleContentLength = count($ruleContent);
                    //根据参数长度, 使用校验, TODO 单个校验目前最多只有1个外界参数, 不包含固定的data和field
                    switch ($ruleContentLength) {
                        case 1 :
                            $res = $this->$method($content, $subField) && $res;
                            if ($res === false) {
                                return $res;
                            }
                            break;
                        case 2 :
                            $res = $this->$method($content, $subField, $ruleContent[1]) && $res;
                            if ($res === false) {
                                return $res;
                            }
                            break;
                        default :
                            break;
                    }
                }
                return $res;
            }
        }
        return false;
    }

    /**
     * @param array $data, 需要进行校验的数组
     * @param array $fieldsRules 校验规则数组,参数用点和|区分 如 array(
     *  "input3" => "numeric|min:10|in:x,y,z|jsonArray",
     *  "input4" => "numeric|max:10|array",
     *  "input3.*.name" => "numeric|min:10|in:x,y,z|jsonArrayParams:numeric@notEmpty@maxLength=3",
     *  "input4.*.test" => "arrayParams:numeric@notEmpty@maxLength=3",
     * )
     * @param array $msg, 传对应字段校验出错后返回的错误信息, 如果没有, 则默认返回校验失败
     * @return bool
     */
    public function validate(&$data, array $fieldsRules=array(), array $msg=array()) :bool {
        $this->msg = $msg;
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
                    case 0 : break;
                    case 1 :
                        $res = $this->$method($data, $field) && $res;
                        break;
                    case 2 :
                        $res = $this->$method($data, $field, $ruleContent[1]) && $res;
                        break;
                    default :
                        //校验规则里携带":"符号
                        unset($ruleContent[0]);
                        $res = $this->$method($data, $field, implode(":", $ruleContent)) && $res;
                        break;
                }
            }
            //如果单个字段校验没通过, 则将信息加入校验数组中
            if($res === false){
                $this->validateRes[$field] = "：内容过长或者格式不正确";
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

    /**
     * 获取校验错误信息
     * @return string
     */
    public function errorMsg() {
        $msg = "";
        if (is_array($this->validateRes) && !empty($this->validateRes)) {
            foreach ($this->validateRes as $k => $v) {
                $field = $k;
                if (isset($this->msg[$k]) && !empty($this->msg[$k])) {
                    $field = $this->msg[$k];
                }
                $msg .= $field . $v . "\r\n";
            }
        }
        return $msg;
    }
}