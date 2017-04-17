<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 14:51
 */

namespace Core\Utility\Validate;


class Rules
{
    const RULE_REQUIRED = 'required';
    const RULE_IS_NUM = 'is_num';
    const RULE_IS_DECIMAL = 'isDecimal';
    const RULE_MAX_NUM = 'max_num';
    const RULE_MAX_LEN = 'max_len';
    const RULE_MIN_NUM = 'min_num';
    const RULE_MIN_LEN = 'min_len';
    const RULE_IS_IP = 'ip';
    const RULE_IS_URL = 'url';
    const RULE_IS_EMAIL = 'email';
    const RULE_IS_ALPHA = 'alpha';
    const RULE_IS_BOOLEAN = 'boolean';
    const RULE_IS_TIMESTAMP = 'timestamp';
    const RULE_REGEX = 'regex';
    const RULE_NOT_EMPTY = 'notEmpty';

    protected $rules = array();
    function add($column,$rule = null,$alertMsg = null,$params = null){
        if($rule == null){
            $this->rules[$column] = null;
        }else{
            $this->rules[$column][$rule] = array(
                "params"=>$params,
                "alertMsg"=>$alertMsg
            );
        }
        return $this;
    }
    function getRules(){
        return $this->rules;
    }
}