<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2017/11/9
 * Time: 23:33
 */

class Memory
{
    private static $instance;

    /**
     * @var \swoole_table
     */
    private $table;

    function __construct()
    {
        $this->table = new \swoole_table(1024);
        $this->table->column('value', swoole_table::TYPE_STRING, 65536); //暂定65536. 目前只定义一个字段value, 可以多加入
        $this->table->create();
        self::$instance = $this;
    }

    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new Memory();
        }
        return self::$instance;
    }

    /**
     * @return mixed|\swoole_table
     */
    function getTable(){
       return $this->table;
    }
}