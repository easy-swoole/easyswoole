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
    const OK = 1;//rpc调用成功
    const SERVICE_REJECT_REQUEST = 0;//服务端拒绝执行，比如缺参数，或是恶意调用
    const SERVICE_NOT_FOUND = -1;//服务端告诉客户端没有该服务
    const SERVICE_GROUP_NOT_FOUND = -2;//服务端告诉客户端该服务不存在该服务组（服务控制器）
    const SERVICE_ACTION_NOT_FOUND = -3;//服务端告诉客户端没有该action
    const SERVICE_ERROR = -4;//服务端告诉客户端服务端出现了错误
    const PACKAGE_ENCRYPT_DECODED_ERROR = -5;//服务端告诉客户端发过来的包openssl解密失败
    const PACKAGE_DECODE_ERROR = -6;//服务端告诉客户端发过来的包无法成功解码为ServiceCaller
    const CLIENT_WAIT_RESPONSE_TIMEOUT = -7;//客户端等待响应超时
    const CLIENT_CONNECT_FAIL = -8;//客户端连接到服务端失败
    const CLIENT_SERVER_NOT_FOUND = -9;//客户端无法找到该服务
}