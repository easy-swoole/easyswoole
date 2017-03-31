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
    const DI_SHUTDOWN_HANDLER = 'DI_SHUTDOWN_HANDLER';
    const DI_SESSION_HANDLER = 'DI_SESSION_HANDLER';
    const DI_SESSION_NAME = 'DI_SESSION_NAME';
    const CONTROLLER_MAX_DEPTH = 'CONTROLLER_MAX_DEPTH';
    const APPLICATION_DIR = 'APPLICATION_DIR';//定义应用目录（以便支持例如多域名部署需求）
}