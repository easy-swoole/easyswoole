<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/20
 * Time: 下午3:50
 */

namespace EasySwoole\Core\Socket;


use EasySwoole\Core\Component\Spl\SplStream;
use EasySwoole\Core\Socket\AbstractInterface\Controller;
use EasySwoole\Core\Socket\Client\Tcp;
use EasySwoole\Core\Socket\Common\CommandBean;

abstract class TcpController extends Controller
{
    private $client;
    function __construct(Tcp $client,CommandBean $request,SplStream $response)
    {
        $this->client = $client;
        parent::__construct( $request, $response);
    }

    protected function client():Tcp
    {
        // TODO: Implement client() method.
        return $this->client;
    }
}