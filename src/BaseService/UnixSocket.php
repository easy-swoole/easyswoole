<?php
/**
 * Created by PhpStorm.
 * User: tioncico
 * Date: 20-4-5
 * Time: 下午11:07
 */

namespace EasySwoole\EasySwoole\BaseService;


use EasySwoole\Component\UnixClient;

class UnixSocket
{
    protected static $timeout = 3;

    public static function unixSocketSendAndRecv(string $socketFile, Package $package, float $timeout = null)
    {
        if ($timeout === null) {
            $timeout = self::$timeout;
        }
        $client = new UnixClient($socketFile);
        $client->send(Protocol::pack(serialize($package)));
        $ret = $client->recv($timeout);
        $client->close();
        if (empty($ret)) {
            return null;
        }
        $data = unserialize(Protocol::unpack($ret));
        if ($data === BaseService::ERROR_PACKAGE_ERROR) {
            throw new Exception('command package error.');
        }elseif ($data===BaseService::ERROR_SERVICE_ERROR){
            throw new Exception('command service error.');
        }
        return $data;
    }
}