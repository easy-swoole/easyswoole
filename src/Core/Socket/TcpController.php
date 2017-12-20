<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/20
 * Time: 下午3:50
 */

namespace EasySwoole\Core\Socket;


use EasySwoole\Core\Socket\Client\Tcp;

abstract class TcpController extends BaseController
{
    private $client;
    final function __construct($fd,$reactorId,$actionName)
    {
        parent::__construct($actionName);
        $this->client = new Tcp($fd,$reactorId);
    }

    function client():Tcp
    {
        // TODO: Implement client() method.
        return $this->client;
    }



}