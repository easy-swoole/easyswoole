<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/3
 * Time: 下午1:49
 */

namespace EasySwoole\Core\Socket\AbstractInterface;

use EasySwoole\Core\Component\Spl\SplStream;
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