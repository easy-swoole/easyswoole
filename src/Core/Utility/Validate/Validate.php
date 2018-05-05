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
            if(!isset($data[$filed]) && in_array(Rule::OPTIONAL,$filedRules)){
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