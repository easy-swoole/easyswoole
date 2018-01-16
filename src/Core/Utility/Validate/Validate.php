<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/3
 * Time: 下午12:55
 */

namespace EasySwoole\Core\Utility\Validate;


use EasySwoole\Core\Component\Spl\SplArray;

class Validate
{
    const ACTIVE_URL = 'ACTIVE_URL';
    const ALPHA = 'ALPHA';
    const BETWEEN = 'BETWEEN';
    const BOOLEAN = 'BOOLEAN';
    const DIFFERENT = 'DIFFERENT';
    const DATE = 'DATE';
    const DATE_AFTER = 'DATE_AFTER';
    const DATE_BEFORE = 'DATE_BEFORE';
    const EMPTY = 'EMPTY';
    const FLOAT = 'FLOAT';
    const IN = 'IN';
    const INTEGER = 'INTEGER';
    const IP = 'IP';
    const IS_ARRAY = 'IS_ARRAY';
    const LEN = 'LEN';
    const NOT_IN = 'NOT_IN';
    const NUMERIC = 'NUMERIC';
    const MAX = 'MAX';
    const MAX_LEN = 'MAX_LEN';
    const MIN = 'MIN';
    const MIN_LEN = 'MIN_LEN';
    const OPTIONAL = 'OPTIONAL';
    const REGEX = 'REGEX';
    const REQUIRED = 'REQUIRED';
    const SAME = 'SAME';
    const TIMESTAMP = 'TIMESTAMP';
    const URL = 'URL';

    public function validate(array $data, Rules $rules): Result
    {
        // TODO: Implement validate() method.
        $splArr = new SplArray($data);
        $ruleData = [];
        $errorList = new ErrorList();
        $allRules = $rules->getRuleList();
        foreach ($allRules as $filed => $item){
            $errorMsg = $item['errorMsg'];
            $filedRules = $item['rule'];
            $filedRules = $filedRules->getRules();
            if(!isset($data[$filed]) && in_array(self::OPTIONAL,$filedRules)){
                continue;
            }else{
                foreach ($filedRules as $rule => $args){
                    $currentData = $splArr->get($filed);
                    if(!Func::$rule($currentData,$data,$args)){
                        $errorList->addError($filed,new ErrorBean(
                            [
                                'filed'=>$filed,
                                'message'=>$errorMsg,
                                'data'=>$currentData,
                                'failRule'=>$rule
                            ]
                        ));
                        break;
                    }else{
                        $ruleData[$filed] = $data[$filed];
                    }
                }
            }
        }
        return new Result($ruleData,$errorList);
    }
}