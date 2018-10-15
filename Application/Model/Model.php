<?php
/**
 * Created by PhpStorm.
 * User: tencent_go
 * Date: 2018/10/10
 * Time: 18:05
 */

namespace App\Model;

use App\Utility\Utils;

/**
 * 基础模型
 * Class Model
 * @package App\Model
 */
class Model
{

    //模型字段
    protected $fields = array();

    //表名称
    protected $table;

    /**
     * read db
     * @var \MysqliDb
     */
    protected $readDb;
    /**
     * master db
     * @var \MysqliDb
     */
    protected $db;

    /**
     * 异步mysql连接
     * @var \App\Vendor\Db\AsyncMysql
     */
    protected $asyncMysql;

    /**
     * mongodb manager
     * @var \MongoDB\Driver\Manager
     */
//    protected $mongoManager;

    /**
     * mongodb 中的数据埋点库名称
     * @var string
     */
//    protected $mongodbName = "datamine.";

    /**
    //     * @var \Elasticsearch\Client
     */
//    protected $elastic;

    function __construct(){

    }

    /**
     * 新建 数据格式 [
     * ["name" => "yang", "age" => 23],
     * ["name" => "yang", "age" => 23]
     * ]
     * @param array $data
     * @return int
     */
    function add(array &$data) {
        $data = Utils::onlyCols($this->fields, $data);
        $res = $this->db->insert($this->table, $data);
        if ($res === false) {
            return 0;
        }
        return $this->db->getInsertId();
    }

    /**
     * 删除
     * @param $id
     * @param $idName
     * @return bool
     */
    function delete($id, $idName) {
        return $this->db->where($idName, $id)->delete($this->table);
    }

    /**
     * 简单的获取列表
     * @param $cols
     * @param $conds
     * @param array $order
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    function gets($cols, $conds, $order = array(), $offset = 0, $limit = 10) {
        $rdb = $this->readDb;
        foreach ($conds as &$cond) {
            if(is_array($cond[1])) {
                $rdb = $rdb->where($cond[0], $cond[1], "IN");
            } else {
                $rdb = $rdb->where($cond[0], $cond[1]);
            }
        }
        if (!empty($order)) {
            $rdb = $rdb->orderBy($order[0], $order[1]);
        }

        //获取全部条数
        if ($limit == -1) {
            return $rdb->get($this->table, null, $cols);
        }

        return $rdb->get($this->table, array($offset, $limit), $cols);
    }

    /**
     * 简单的获取总数数量
     * @param $conds
     * @return array
     * @throws \Exception
     */
    function getCount($conds) {
        $rdb = $this->readDb;
        foreach ($conds as &$cond) {
            if(is_array($cond[1])) {
                $rdb = $rdb->where($cond[0], $cond[1], "IN");
            } else {
                $rdb = $rdb->where($cond[0], $cond[1]);
            }
        }

        //获取总条数
        return $rdb->getValue($this->table, "COUNT(1)");

    }

    /**
     * 简单获取单条数据
     * @param $cols
     * @param $conds
     * @return mixed
     */
    function get($cols, $conds) {
        $rdb = $this->readDb;
        if (empty($cols)) {
            $cols = "*";
        }else {
            $cols = array_intersect($cols, $this->fields);
        }
        foreach ($conds as &$cond) {
            if(is_array($cond[1])) {
                $rdb = $rdb->where($cond[0], $cond[1], "IN");
            } else {
                $rdb = $rdb->where($cond[0], $cond[1]);
            }
        }
        //单条数据
        return $rdb->getOne($this->table, $cols);
    }

    /**
     * 简单的更新数据
     * @param $data
     * @param $conds
     * @return bool
     */
    function update(&$data, $conds) {
        $data = Utils::onlyCols($this->fields, $data);
        $rdb = $this->db;
        foreach ($conds as &$cond) {
            if(is_array($cond[1])) {
                $rdb = $rdb->where($cond[0], $cond[1], "IN");
            } else {
                $rdb = $rdb->where($cond[0], $cond[1]);
            }
        }
        //更细数据
        return $rdb->update($this->table, $data);
    }

}