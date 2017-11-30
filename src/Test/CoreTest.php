<?php

/**
 * Created by PhpStorm.
 * User: sl
 * Date: 2017/11/29
 * Time: 上午10:32
 * Hope deferred makes the heart sick,but desire fulfilled is a tree of life.
 */
class CoreTest extends \PHPUnit\Framework\TestCase
{

    public function testFrameWorkInitialize(){
        require_once __DIR__.'/../Core/Core.php';
        $server = \Core\Core::getInstance()->frameWorkInitialize();
    }

}