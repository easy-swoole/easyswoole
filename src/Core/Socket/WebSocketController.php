<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/20
 * Time: 下午3:51
 */

namespace EasySwoole\Core\Socket;


use EasySwoole\Core\Socket\Client\WebSocket;

abstract class WebSocketController extends AbstractController
{
    private $client;
    final function __construct(WebSocket $client,array $args)
    {
        parent::__construct($args);
        $this->client = $client;
    }

    function client():WebSocket
    {
        // TODO: Implement client() method.
        return $this->client;
    }
}