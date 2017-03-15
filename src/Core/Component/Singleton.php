<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/2/2
 * Time: 上午12:01
 */

namespace Core\Component;


trait Singleton
{
    protected static $instance;
    static function getInstance() {
        if(self::$instance instanceof static){
            return self::$instance;
        }else{
            $args = func_get_args ();
            $class = static::class;
            $reflection = new \ReflectionClass ( $class );
            self::$instance = $reflection->newInstanceArgs ( $args );
            return self::$instance;
        }
    }
}