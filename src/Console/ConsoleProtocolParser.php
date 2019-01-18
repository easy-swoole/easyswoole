<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/20
 * Time: 下午11:29
 */

namespace EasySwoole\EasySwoole\Console;


use EasySwoole\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

class ConsoleProtocolParser implements ParserInterface
{
    public function decode($raw, $client): ?Caller
    {
        // TODO: Implement decode() method.
        $caller =  new Caller();
        $data = self::unpack($raw);
        $arr = unserialize($data);
        $caller->setAction(array_shift($arr));
        $caller->setControllerClass(ConsoleTcpController::class);
        $caller->setArgs($arr);
        return $caller;
    }

    public function encode(Response $response, $client): ?string
    {
        // TODO: Implement encode() method.
        $str = $response->getMessage();
        if(empty($str)){
            $str = 'empty response';
        }
        return self::pack(serialize($str));
    }

    public static function pack(string $data):string
    {
        return pack('N', strlen($data)).$data;
    }

    public static function unpack(string $data):string
    {
        return substr($data,'4');
    }
}