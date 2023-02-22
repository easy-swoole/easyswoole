<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:24
 */

namespace EasySwoole\EasySwoole;


class SysConst
{
    const EASYSWOOLE_VERSION = '3.6.4-dev';

    const LOGGER_HANDLER = 'LOGGER_HANDLER';
    const ERROR_HANDLER = 'ERROR_HANDLER';
    const ERROR_REPORT_LEVEL = 'ERROR_REPORT_LEVEL';
    const TRIGGER_HANDLER = 'TRIGGER_HANDLER';

    const SHUTDOWN_FUNCTION = 'SHUTDOWN_FUNCTION';

    const HTTP_CONTROLLER_NAMESPACE = 'HTTP_CONTROLLER_NAMESPACE';
    const HTTP_CONTROLLER_MAX_DEPTH = 'CONTROLLER_MAX_DEPTH';
    const HTTP_EXCEPTION_HANDLER = 'HTTP_EXCEPTION_HANDLER';
    const HTTP_CONTROLLER_POOL_MAX_NUM = 'HTTP_CONTROLLER_POOL_MAX_NUM';
    const HTTP_CONTROLLER_POOL_WAIT_TIME = 'HTTP_CONTROLLER_POOL_WAIT_TIME';
    const HTTP_GLOBAL_ON_REQUEST = 'HTTP_GLOBAL_ON_REQUEST';
    const HTTP_GLOBAL_AFTER_REQUEST = 'HTTP_GLOBAL_AFTER_REQUEST';
}
