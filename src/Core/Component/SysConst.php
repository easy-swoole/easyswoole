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
    const DI_SESSION_HANDLER = 'DI_SESSION_HANDLER';
    const CONTROLLER_MAX_DEPTH = 'CONTROLLER_MAX_DEPTH';
    const APPLICATION_DIR = 'APPLICATION_DIR';//定义应用目录（以便支持例如多域名部署需求）
    const SHARE_MEMORY_FILE = 'SHARE_MEMORY_FILE';
    const TEMP_DIRECTORY = 'TEMP_DIRECTORY';
    const LOG_DIRECTORY = 'LOG_DIRECTORY';
    const VERSION_CONTROL = 'VERSION_CONTROL';
}