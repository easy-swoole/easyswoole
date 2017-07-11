<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/8
 * Time: 上午12:41
 */

namespace App\Model\Goods;


use App\Model\ModelDb;
use App\Utility\Mysql;

class Goods
{
    protected $db;
    protected $table = 'goods_list';
    function __construct()
    {
        $this->db = Mysql::getInstance()->getDb();
    }
    function add(GoodsBean $goodsBean){
        return $this->db->insert($this->table,$goodsBean->toArray());
    }
    function update($id,$data){
        return $this->db->where("id",$id)->update($this->table,$data);
    }
}