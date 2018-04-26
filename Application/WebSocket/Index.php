<?php
/**
 * Created by PhpStorm.
 * User: 111
 * Date: 2018/4/26
 * Time: 11:38
 */

namespace App\WebSocket;

use EasySwoole\Core\Component\Spl\SplStream;
use EasySwoole\Core\Socket\Client\WebSocket;
use EasySwoole\Core\Socket\Common\CommandBean;

class Index extends BaseWsController
{
    function __construct(WebSocket $client, CommandBean $request, SplStream $response)
    {
        parent::__construct($client, $request, $response);
    }

    function actionNotFound(?string $actionName)
    {
        $this->response()->write("action call {$actionName} not found");
    }

    function hello()
    {
        $this->response()->write('call hello with arg:'.$this->request()->getArg('content'));
    }

    public function who(){
        $this->response()->write('your fd is '.$this->client()->getFd());
    }

}