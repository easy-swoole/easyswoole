<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/9/3
 * Time: 下午9:37
 */

namespace Core\Utility\Validate;


class Field
{
    protected $currentRule = null;
    protected $rule = array();
    protected $msg = array(
        '__default__'=>null
    );

    function withMsg($msg){
        if(isset($this->currentRule)){
            $this->msg[$this->currentRule] = $msg;
            $this->currentRule = null;
        }else{
            $this->msg['__default__'] = $msg;
        }
        return $this;
    }

    function withRule($rule,...$arg){
        $this->currentRule = $rule;
        $this->rule[$rule] = $arg;
        return $this;
    }

    function getRule(){
        return $this->rule;
    }

    function getMsg(){
        return $this->msg;
    }

}