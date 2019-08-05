<?php


namespace EasySwoole\EasySwoole\Test;


use EasySwoole\EasySwoole\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    function testAll()
    {
        Config::getInstance()->setConf('string','string');
        Config::getInstance()->setConf('int',1);
        Config::getInstance()->setConf('array',[
            'key'=>'key',
            'array'=>[
                'sub'=>1
            ]
        ]);
        $this->assertEquals('string',Config::getInstance()->getConf('string'));
        $this->assertEquals(1,Config::getInstance()->getConf('int'));
        $this->assertEquals([
            'key'=>'key',
            'array'=>[
                'sub'=>1
            ]
        ],Config::getInstance()->getConf('array'));
        $this->assertEquals("key",Config::getInstance()->getConf('array.key'));
        $this->assertEquals([
            'sub'=>1
        ],Config::getInstance()->getConf('array.array'));
        $this->assertEquals(1,Config::getInstance()->getConf('array.array.sub'));
        $this->assertEquals(null,Config::getInstance()->getConf('null'));
        $this->assertEquals(null,Config::getInstance()->getConf('array.null'));
    }
}