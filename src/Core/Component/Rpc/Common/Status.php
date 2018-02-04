<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/24
 * Time: 下午1:15
 */

namespace EasySwoole\Core\Component\Rpc\Common;


class Status
{
    const OK = 1;
    const SERVICE_NOT_FOUND = 0;
    const CONNECT_FAIL = -1;
    const TIMEOUT = -2;
    const ACTION_NOT_FOUND = -3;
    const SERVER_ERROR = -4;
}