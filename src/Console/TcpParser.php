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
        $caller =  new Caller();
        $data = self::unpack($raw);
        $arr = json_decode($data,true);
        if(!is_array($arr)){
            $arr = [
                'action'=>'help',
                'controller'=>TcpController::class,
                'args'=>[
                ]
            ];
        }else{
            $arr['controller'] = TcpController::class;
        }
        $caller->setAction($arr['action']);
        $caller->setControllerClass($arr['controller']);
        $caller->setArgs($arr['args']);
        return $caller;
    }

    public function encode(Response $response, $client): ?string
    {
        // TODO: Implement encode() method.
        $str = $response->getMessage();
        if(empty($str)){
            $str = 'empty response';
        }
        return self::pack(trim($str));
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