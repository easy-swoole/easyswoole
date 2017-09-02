<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/8
 * Time: 下午8:06
 */

namespace Core\Utility\Validate;

use Core\Component\Spl\SplBean;

class ColumnBean extends SplBean
{
    protected $errorMsg;
    protected $ruleMap = array();

    function withErrorMsg($msg){
        $this->errorMsg = $msg;
        return $this;
    }

    function addRule($rule,array $args = array(),$errorMsg = null){
        $this->ruleMap[$rule] = array(
            "args"=>$args,
            "msg"=>$errorMsg,
        );
        return $this;
    }

    protected function initialize()
    {
        // TODO: Implement initialize() method.
    }
}