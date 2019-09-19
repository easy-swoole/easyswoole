<?php


namespace EasySwoole\EasySwoole\Test;


use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Test\Config\TestConfig1;
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

    public function testStorageHandler() {
        $config = Config::getInstance()->storageHandler(new TestConfig1());
        $this->assertEquals(
            [
                'name' => 'easyswoole',
                'action' => 'getConf'
            ],
            $config->getConf()
        );
    }

    public function testLoad() {
        $config = Config::getInstance(new TestConfig1());
        $config->load([
            'name' => 'easyswoole',
            'action' => 'load'
        ]);
        $this->assertEquals([
            'name' => 'easyswoole',
            'action' => 'load'
        ], $config->getConf());
    }

    public function testMerge() {
        $config = Config::getInstance(new TestConfig1());
        $config->merge([
            'name1' => 'es',
            'action1' => 'merge'
        ]);
        $this->assertEquals([
            'name' => 'easyswoole',
            'action' => 'load',
            'name1' => 'es',
            'action1' => 'merge'
        ], $config->getConf());
    }

    public function testLoadFile() {
        $config = Config::getInstance(new TestConfig1());
        $config->load([
            'name' => 'easyswoole'
        ]);
        // 改成本机路径
        $config->loadFile('xx/TestConfig2.php');
        $this->assertEquals(
            [
                'name' => 'easyswoole',
                'testconfig2' => [
                    'alias' => 'es'
                ]
            ],
            $config->getConf()
        );

        // TODO: merge

    }

    public function testLoadEnv() {
        $config = Config::getInstance(new TestConfig1());
        // 改成本机路径
        $config->loadEnv('xx/TestConfig2.php');
        $this->assertEquals(
            [
                'alias' => 'es'
            ],
            $config->getConf()
        );
    }

    public function testClear() {
        $config = Config::getInstance(new TestConfig1());
        $config->clear();
        $this->assertEmpty($config->getConf());
    }

}