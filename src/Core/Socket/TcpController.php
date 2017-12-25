<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/20
 * Time: 下午3:50
 */

namespace EasySwoole\Core\Socket;


use EasySwoole\Core\Socket\Client\Tcp;

abstract class TcpController extends AbstractController
{
    private $client;
    final function __construct(Tcp $client,array $args)
    {
        parent::__construct($args);
        $this->client = $client;
    }

    function client():Tcp
    {
        // TODO: Implement client() method.
        return $this->client;
    }
}