<?php

namespace EasySwoole\EasySwoole\Test;

use EasySwoole\EasySwoole\Core;
use PHPUnit\Framework\TestCase;

class CoreTest extends TestCase
{
    function runTest()
    {
        Core::getInstance()->initialize();
        return parent::runTest();
    }

    function testDev()
    {
        global $argv;
        if(in_array('produce',$argv)){
            $this->assertEquals(false,Core::getInstance()->isDev());
        }else{
            $this->assertEquals(true,Core::getInstance()->isDev());
        }
    }

    function testTempDir()
    {
        $this->assertEquals(EASYSWOOLE_ROOT.'/Temp',EASYSWOOLE_TEMP_DIR);
    }

    public function testSetIsDev() {
        Core::getInstance()->setIsDev(true);
        $this->assertTrue(Core::getInstance()->isDev());
    }

    public function testCreateServer() {
        $server = Core::getInstance()->createServer();
        $this->assertEquals('EasySwoole\EasySwoole\Core', get_class($server));
    }

}