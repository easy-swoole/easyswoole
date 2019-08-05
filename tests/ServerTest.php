<?php


namespace EasySwoole\EasySwoole\Test;


use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    function testServer()
    {
        $this->assertInstanceOf(\swoole_http_server::class,ServerManager::getInstance()->getSwooleServer());
        $this->assertEquals([
            'worker_num' => 8,
            'task_worker_num' => 8,
            'reload_async' => true,
            'task_enable_coroutine' => true,
            'max_wait_time'=>3
        ],ServerManager::getInstance()->getSwooleServer()->setting);
    }

    function testSubPort()
    {
        $register = ServerManager::getInstance()->addServer('tcp',9500);
        $this->assertInstanceOf(EventRegister::class,$register);
        $register->add($register::onConnect,'onconnect');
        $this->assertEquals(['onconnect'],$register->get($register::onConnect));
        $sub = ServerManager::getInstance()->getSwooleServer('tcp');
        $this->assertInstanceOf(\swoole_server_port::class,$sub);
    }
}