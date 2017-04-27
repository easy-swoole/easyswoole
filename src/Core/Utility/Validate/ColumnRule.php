<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/4/27
 * Time: 下午12:39
 */

namespace Core\Utility\Validate;


class ColumnRule
{
    private $ruleMap;
    private $column;
    private static $instance;
    static function getInstance(& $ruleMap, $column){
        if(!isset(self::$instance)){
            self::$instance = new ColumnRule($ruleMap);
        }
        self::$instance->column = $column;
        return self::$instance;
    }
    function __construct(& $ruleMap)
    {
        $this->ruleMap = & $ruleMap;
    }

    function withRule($ruleName,$args = array()){
        $this->ruleMap[$this->column]['rules'][$ruleName] = $args;
        $this->ruleMap[$this->column]['errorMsg'] = null;
        return $this;
    }
    function withErrorMsg($msg){
        $this->ruleMap[$this->column]['errorMsg'] = $msg;
        return $this;
    }
}