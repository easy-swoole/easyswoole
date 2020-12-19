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

    function testTempDir()
    {
        $this->assertEquals(EASYSWOOLE_ROOT.'/Temp',EASYSWOOLE_TEMP_DIR);
    }

}