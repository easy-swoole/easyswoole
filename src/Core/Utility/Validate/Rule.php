<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/3
 * Time: 下午1:26
 */

namespace EasySwoole\Core\Utility\Validate;


class Rule
{
    private $rules = [];
    public function withRule(string $rule,...$args):Rule
    {
        $this->rules[$rule] = $args;
        return $this;
    }

    public function getRules():array
    {
        return $this->rules;
    }
}