<?php


namespace EasySwoole\EasySwoole\Test;


use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use PHPUnit\Framework\TestCase;

class CoreTest extends TestCase
{
    function setUp()
    {
        defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT', dirname(__file__));

        Core::getInstance()->initialize();
    }

    function testDev(){
        $this->assertEquals(Core::getInstance()->isDev(),Config::getInstance()->getConf('IS_DEV'));
    }
    function testProduce(){
        Core::getInstance()->setIsDev(false)->initialize();
        $this->assertFalse(Config::getInstance()->getConf('IS_DEV'));
        Core::getInstance()->setIsDev(true);
        Core::getInstance()->loadEnv();
        $this->assertTrue(Config::getInstance()->getConf('IS_DEV'));
    }
}