<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\EasySwoole\Test;


use EasySwoole\Config\AbstractConfig;
use EasySwoole\Config\TableConfig;
use EasySwoole\EasySwoole\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testStorageHandler()
    {
        $config = new TableConfig();
        $this->assertInstanceOf(AbstractConfig::class, Config::getInstance()->storageHandler($config));
    }

    public function testGetConf()
    {
        $conf = Config::getInstance()->getConf();
        $this->assertEmpty($conf);
    }

    public function testSetConf()
    {
        $bool = Config::getInstance()->setConf('test', 'easyswoole.php');
        $this->assertTrue($bool);
        $this->assertEquals('easyswoole.php', Config::getInstance()->getConf('test'));
    }

    public function testLoad()
    {
        $bool = Config::getInstance()->load(['test' => 'easyswoole.php']);
        $this->assertTrue($bool);

        $conf = Config::getInstance()->getConf();
        $this->assertEquals(['test' => 'easyswoole.php'], $conf);
    }

    public function testMerge()
    {
        $bool = Config::getInstance()->merge(['test' => 'easyswoole.php']);
        $this->assertTrue($bool);

        $conf = Config::getInstance()->getConf();
        $this->assertEquals(['test' => 'easyswoole.php'], $conf);
    }

    public function testClear()
    {
        $bool = Config::getInstance()->clear();
        $this->assertTrue($bool);
    }
}