<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/27
 * Time: 下午12:39
 */

namespace Core\Utility\Validate;


use Core\Component\Spl\SplArray;
use Core\Component\Spl\SplString;

class Validate
{
    private $ruleMap = array();
    private $dataForValidate;
    private $resultError =  null;
    const RULE_REQUIRE = 'required';
    private $funcMap;

    /**
     * Validate constructor.
     * @param array $dataForValidate
     * @param array|null $rules
     */
    function __construct(array $dataForValidate = array(), array $rules = array())
    {
        $this->initFuncMap();
        $this->dataForValidate = $dataForValidate;
        $this->parserUserRules($rules);
    }

    function addColumn($column){
        return ColumnRule::getInstance($this->ruleMap,$column);
    }

    function validate(array $dataForValidate = array(), array $rules = array()){
        if(!empty($rules)){
            $this->parserUserRules($rules);
        }
        if(!empty($dataForValidate)){
            $this->dataForValidate = $dataForValidate;
        }
        $result = new SplArray();
        foreach ($this->ruleMap as $column => $columnInfo){
            $data = $this->accessData($column);
            $hasError = false;
            foreach ($columnInfo['rules'] as $rule => $args){
                $isValidate = $this->callFunc($data,$rule,$args);
                if(!$isValidate){
                    $this->resultError[$column]['failRules'][] = $rule;
                    $this->resultError[$column]['data'] = $data;
                    $this->resultError[$column]['errorMsg'] = $columnInfo['errorMsg'];
                    $hasError = true;
                }
            }
            if(!$hasError){
                $result->set($column,$data);
            }
        }
        return new Result($result->getArrayCopy(),$this->resultError);
    }

    private function accessData($column){
        $column = new SplString($column);
        $list = $column->explode(".");
        $temp = $this->dataForValidate;
        foreach ($list as $item){
            if(isset($temp[$item])){
                $temp = $temp[$item];
            }else{
                return null;
            }
        }
        return $temp;
    }

    /*
     * 用于解析用户的规则数组
     * @param array $userArray
     * array(
     *      "field"=>"rule1|rule2:args1,args2@fieldErrorMsg"
     * );
     */
    private function parserUserRules($userArray){
        foreach ($userArray as $key => $item){
            $item = new SplString($item);
            //存在错误信息
            if($item->exist("@")){
                $item = $item->explode("@");
                $this->addColumn($key)->withErrorMsg($item[1]);
                $item = new SplString($item[0]);
            }
            $itemRules = $item->explode("|");
            foreach ($itemRules as $rule){
                $rule = new SplString($rule);
                //存在参数
                if($rule->exist(":")){
                    $rule = $rule->explode(":");
                    $this->addColumn($key)->withRule($rule[0],(array)(new SplString($rule[1]))->explode(","));
                }else{
                    $this->addColumn($key)->withRule((string)$rule);
                }
            }
        }
    }
    private function callFunc($data,$rule,$args){
        if(isset($this->funcMap[$rule])){
            $func = $this->funcMap[$rule];
            try{
                return call_user_func($func,$data,$args);
            }catch (\Exception $exception){
                trigger_error($exception);
                return false;
            }

        }else{
            trigger_error("validate func for rule {$rule} is not matches");
            return false;
        }
    }
    private function initFuncMap(){
        $this->funcMap = array(
            "required"=>function($data,$args){
                if($data !== null){
                    return true;
                }else{
                    return false;
                }
            },
            "array"=>function($data){
                return is_array($data) ? true : false;
            },
            "between"=>function($data,$args){
                if(($data > $args[0]) && ($data < $args[1])){
                    return true;
                }else{
                    return false;
                }
            },
            'maxLen'=>function(){

            },
            "minLen"=>function(){

            },
            "alpha"=>function(){

            },
            "boolean"=>function(){

            },
            "minNum"=>function(){

            },
            "maxNum"=>function(){

            },
            "timestamp"=>function(){

            },
            "regex"=>function(){

            },
            "decimal"=>function(){

            }


        );
    }
}