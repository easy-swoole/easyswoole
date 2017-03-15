<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/3
 * Time: 下午7:38
 */

namespace Core\Component;


class SysConst
{
    /*
     * DI开头为依赖注入键值名称
     */
    const DI_ERROR_HANDLER = 'DI_ERROR_HANDLER';
    const DI_LOGGER_WRITER = 'DI_LOGGER_WRITER';
    const DI_EXCEPTION_HANDLER = 'DI_EXCEPTION_HANDLER';
    const DI_SHUTDOWN_HANDLER = 'DI_SHUTDOWN_HANDLER';

    const CONTROLLER_MAX_DEPTH = 'CONTROLLER_MAX_DEPTH';
}