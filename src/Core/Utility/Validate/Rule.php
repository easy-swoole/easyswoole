<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/9
 * Time: 下午12:33
 */

namespace Core\Utility\Validate;


use Core\Component\Spl\SplArray;

class Rule
{
    const ACTIVE_URL = 'ACTIVE_URL';
    const ALPHA = 'ALPHA';
    const BETWEEN = 'BETWEEN';
    const BOOLEAN = 'BOOLEAN';
    const DATE = 'DATE';
    const DATE_AFTER = 'DATE_AFTER';
    const DATE_BEFORE = 'DATE_BEFORE';
    const DIFFERENT = 'DIFFERENT';
    const FLOAT = 'FLOAT';
    const IN = 'IN';
    const INTEGER = 'INTEGER';
    const IP = 'IP';
    const ARRAY_ = 'ARRAY_';
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
    const REQUIRED_IF = 'REQUIRED_IF';
    const REQUIRE_WITH = 'REQUIRE_WITH';
    const REQUIRE_WITH_ALL = 'REQUIRE_WITH_ALL';
    const REQUIRE_WITHOUT = 'REQUIRE_WITHOUT';
    const REQUIRE_WITHOUT_ALL = 'REQUIRE_WITHOUT_ALL';
    const SAME = 'SAME';
    const TIMESTAMP = 'TIMESTAMP';
    const URL = 'URL';
}