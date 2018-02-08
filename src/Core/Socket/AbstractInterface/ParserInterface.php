<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/22
 * Time: 下午1:00
 */

namespace EasySwoole\Core\Socket\AbstractInterface;

interface ParserInterface
{
    /*
     * 若返回EasySwoole\Core\Socket\Common\CommandBean，则为解析成功，
     * 若返回NULL，则调用parser error 回调
     * 若返回string，则encode后返回给客户端
     */
    public function decode($raw,$client);

    /*
     * $commandBean为请求decode后的结果，解决需要客户端主动动态密钥的加密传输场景，
     */
    public function encode(string $raw,$client,$commandBean):?string ;
}