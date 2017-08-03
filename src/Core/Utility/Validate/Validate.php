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
            //优先检索 OPTIONAL规则。
            if(isset($checkRuleMap['ruleMap']['OPTIONAL'])){
                //当某个参数可选时
                if($this->columnInSplArrIsNull($data, $column)){
                    continue;
                }
                //OPTIONAL规则不参与实际检验
                unset($checkRuleMap['ruleMap']['OPTIONAL']);
            }

            foreach ($checkRuleMap['ruleMap'] as $rule => $ruleData){
                if (strpos($column, '*') !== false){
                    //column中包含*的情况
                    $values = $data->get($column);
//                    var_dump($values);
                    //获取出来是数组的时候   多维数组转一维数组    有可能是null就转成一维数组[null]
                    if (is_array($values)){
                        $values = $this->getOneDimensionalArray($values);
                    }else{
                        $values = array($values);
                    }
//                    var_dump($values);

                    foreach ($values as $value){
                        $data->set('tmpKey', $value);
                        if(!Func::$rule('tmpKey', $data,$ruleData['args'])){
                            $msg = $ruleData['msg'] ? $ruleData['msg'] : $checkRuleMap['errorMsg'];
                            $errorMessages[$column][] = $msg;
                            //这个字段遇到错误就不检测了直接跳出
                            break;
                        }
                    }

                }else {
                    if(!Func::$rule($column,$data,$ruleData['args'])){
                        $msg = $ruleData['msg'] ? $ruleData['msg'] : $checkRuleMap['errorMsg'];
                        $errorMessages[$column][] = $msg;
                    }
                }
            }
        }
        return new Message($errorMessages);
    }

    //多维数组转一维数组
    private function getOneDimensionalArray($array){
        $arr = array();
        foreach ($array as $key => $val) {
            if( is_array($val) ) {
                $arr = array_merge($arr, $this->getOneDimensionalArray($val));
            } else {
                $arr[] = $val;
            }
        }
        return $arr;
    }

    //判断 数组splarray某个字段是否为空    null  多级数组转一维数组为[null]都算空      在option判断使用
    private function columnInSplArrIsNull(SplArray $array, $column){
        $value = $array->get($column);

        if($value === null){
            return true;
        }

        if (is_array($value)){
            $value = $this->getOneDimensionalArray($value);
            if (count($value) === 1 && $value[0] === null){
                return true;
            }
        }

        return false;
    }
}