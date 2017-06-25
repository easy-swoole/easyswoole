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
    protected $ruleMap = array();
    function __construct(array $ruleMap = array())
    {
        $this->ruleMap = $ruleMap;
    }
    function addColumn($column){
        /*
         * 用$ret变量  方便ide识别返回数据类型
         * 其次，对象变量，理解为地址引用即可，即$ret外部修改后，依旧可以影响$ruleMap[$column]的值
         */
        $ret = new ColumnBean();
        $this->ruleMap[$column] = $ret;
        return $ret;
    }
    function validate(array $data){
        $errorMessages = array();
        $data = new SplArray($data);
        foreach ($this->ruleMap as $column => $columnBean){
            $checkRuleMap = $columnBean->toArray();
            foreach ($checkRuleMap['ruleMap'] as $rule => $ruleData){
                if(!Func::$rule($column,$data,$ruleData['args'])){
                    $msg = $ruleData['msg'] ? $ruleData['msg'] : $checkRuleMap['errorMsg'];
                    $errorMessages[$column][] = $msg;
                }
            }
        }
        return new Message($errorMessages);
    }
}