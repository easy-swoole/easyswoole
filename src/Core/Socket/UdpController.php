<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/20
 * Time: 下午3:49
 */

namespace EasySwoole\Core\Socket;


use EasySwoole\Core\Socket\Client\Udp;

abstract class UdpController extends AbstractController
{

    private $client;
    final function __construct(Udp $client,array $args)
    {
        parent::__construct($args);
        $this->client = $client;
    }

    function client():Udp
    {
        // TODO: Implement client() method.
        return $this->client;
    }
}