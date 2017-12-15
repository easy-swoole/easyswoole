<?php
/**
 * swoole-ide-helper
 * Author: Wudi <wudi@51idc.com>
 * Datetime: 20/07/2017
 */

namespace Swoole\Coroutine;

// 用于multi($mode)方法，默认为SWOOLE_REDIS_MODE_MULTI模式：
define('SWOOLE_REDIS_MODE_MULTI', 1);
define('SWOOLE_REDIS_MODE_PIPELINE', 1);

// 用于判断 type() 命令的返回值
define('SWOOLE_REDIS_TYPE_NOT_FOUND', 1);
define('SWOOLE_REDIS_TYPE_STRING', 1);
define('SWOOLE_REDIS_TYPE_SET', 1);
define('SWOOLE_REDIS_TYPE_LIST', 1);
define('SWOOLE_REDIS_TYPE_ZSET', 1);
define('SWOOLE_REDIS_TYPE_HASH', 1);

class Redis extends \Redis
{
    public $errCode;
    public $errMsg;
}