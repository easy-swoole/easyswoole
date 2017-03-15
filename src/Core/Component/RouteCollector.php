<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 14:39
 */

namespace Core\Component;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std;

class RouteCollector extends \FastRoute\RouteCollector
{
    protected static $self_instance;
    static function getInstance(){
        if(!isset(self::$self_instance)){
            self::$self_instance = new static(new Std(),new GroupCountBased());
        }
        return self::$self_instance;
    }
}