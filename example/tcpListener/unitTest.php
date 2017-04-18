<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/22
 * Time: 下午9:46
 */
require_once 'Core/Core.php';
\Core\Core::getInstance()->frameWorkInitialize();
$client = new swoole_client(SWOOLE_SOCK_TCP);
$count = 0;
//$client->set(array('open_eof_check' => true, 'package_eof' => "\r\n\r\n"));

//$client = new swoole_client(SWOOLE_SOCK_UNIX_DGRAM, SWOOLE_SOCK_SYNC); //同步阻塞
//if (!$client->connect(dirname(__DIR__).'/server/svr.sock', 0, -1, 1))

if (!$client->connect('127.0.0.1', 9502, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}

$client->send("hello world");

//for($i=0; $i < 3; $i ++)
echo $client->recv();
sleep(1);
$client->close();
$count++;
