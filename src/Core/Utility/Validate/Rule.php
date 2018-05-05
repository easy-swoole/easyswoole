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