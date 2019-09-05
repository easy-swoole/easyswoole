<?php


namespace EasySwoole\EasySwoole\Test;


use EasySwoole\EasySwoole\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $time;
    private $array;
    function runTest()
    {
        $this->time = time();
        $this->array = [
            'a'=>1,
            'b'=>[
                'b1'=>'bv',
                'b2'=>'bb'
            ]
        ];
        return parent::runTest();
    }

    function testSet()
    {

        $this->assertEquals(true,Config::getInstance()->setConf('key',$this->time));
        $this->assertEquals(true,Config::getInstance()->setConf('array',$this->array));
    }

    function testGet()
    {
        $this->assertEquals($this->time,Config::getInstance()->getConf('key'));
        $this->assertEquals($this->array,Config::getInstance()->getConf('array'));
    }



}