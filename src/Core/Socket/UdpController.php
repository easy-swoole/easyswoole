<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/20
 * Time: 下午3:49
 */

namespace EasySwoole\Core\Socket;


use EasySwoole\Core\Component\Spl\SplStream;
use EasySwoole\Core\Socket\AbstractInterface\Controller;
use EasySwoole\Core\Socket\Client\Udp;
use EasySwoole\Core\Socket\Common\CommandBean;

abstract class UdpController extends Controller
{

    private $client;
    function __construct(Udp $client,CommandBean $request,SplStream $response)
    {
        $this->client = $client;
        parent::__construct($request, $response);
    }

    protected function client():Udp
    {
        // TODO: Implement client() method.
        return $this->client;
    }
}