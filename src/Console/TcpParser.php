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

class TcpParser implements ParserInterface
{
    public function decode($raw, $client): ?Caller
    {
        // TODO: Implement decode() method.
        $data = substr($raw,'4');
        $arr = json_decode($data,true);
        if(!is_array($arr)){
            $arr = [
                'action'=>'decodeError',
                'controller'=>DefaultController::class,
                'args'=>[
                    'raw'=>$raw
                ]
            ];
        }
        $caller =  new Caller();
        $caller->setAction($arr['action']);
        $caller->setControllerClass($arr['controller']);
        $caller->setArgs($arr['args']);
        return $caller;
    }

    public function encode(Response $response, $client): ?string
    {
        // TODO: Implement encode() method.
        $sendStr = json_encode($response->getResult(),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return pack('N', strlen($sendStr)).$sendStr;
    }
}