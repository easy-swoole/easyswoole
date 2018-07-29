<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/28
 * Time: 下午5:42
 */

namespace EasySwoole\EasySwoole\Swoole\Memory;


use EasySwoole\Component\Singleton;
use Swoole\Table;

class TableManager
{
    use Singleton;

    private $list = [];


    /**
     * @param $name
     * @param array $columns    ['col'=>['type'=>Table::TYPE_STRING,'size'=>1]]
     * @param int $size
     */
    public function add($name,array $columns,$size = 1024):void
    {
        if(!isset($this->list[$name])){
            $table = new Table($size);
            foreach ($columns as $column => $item){
                $table->column($column,$item['type'],$item['size']);
            }
            $table->create();
            $this->list[$name] = $table;
        }
    }

    public function get($name):?Table
    {
        if(isset($this->list[$name])){
            return $this->list[$name];
        }else{
            return null;
        }
    }
}