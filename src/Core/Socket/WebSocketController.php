<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/20
 * Time: 下午3:51
 */

namespace EasySwoole\Core\Socket;


use EasySwoole\Core\Component\Spl\SplStream;
use EasySwoole\Core\Socket\AbstractInterface\Controller;
use EasySwoole\Core\Socket\Client\WebSocket;
use EasySwoole\Core\Socket\Common\CommandBean;

abstract class WebSocketController extends Controller
{
    private $client;
    function __construct(WebSocket $client,CommandBean $request,SplStream $response)
    {
        parent::__construct( $request, $response);
        $this->client = $client;
    }

    protected function client():WebSocket
    {
        // TODO: Implement client() method.
        return $this->client;
    }
}