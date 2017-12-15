<?php
/**
 * swoole-ide-helper
 * Author: Wudi <wudi@51idc.com>
 * Datetime: 20/07/2017
 */

namespace Swoole\Coroutine;

/**
 * Class Mysql
 * 需要在编译swoole时增加--enable-coroutine来开启此功能
 * @link https://wiki.swoole.com/wiki/page/p-coroutine_mysql.html
 *
 * @package Swoole\Coroutine
 */
class Mysql
{
    /** @var array 连接信息，保存的是传递给构造函数的数组 */
    public $serverInfo;

    /** @var integer 连接使用的文件描述符 */
    public $sock;

    /** @var bool 是否连接上了MySQL服务器 */
    public $connected;

    /** @var string 发生在sock上的连接错误信息 */
    public $connect_error;
    /** @var integer 发生在sock上的连接错误码 */
    public $connect_errno;

    /** @var string MySQL服务器返回的错误信息 */
    public $error;
    /** @var integer MySQL服务器返回的错误代码 */
    public $errno;

    /** @var integer 影响的行数 */
    public $affected_rows;

    /** @var  integer 最后一个插入的记录id */
    public $insert_id;

    /**
     * 建立 MySQL 连接
     * @link https://wiki.swoole.com/wiki/page/595.html
     *
     * @param array $serverInfo [
     *         'host' => 'MySQL IP地址',
     *         'user' => '数据用户',
     *         'password' => '数据库密码',
     *         'database' => '数据库名',
     *         'port'    => 'MySQL端口 默认3306 可选参数',
     *         'timeout' => '建立连接超时时间',
     *         'charset' => '字符集'
     * ]
     * @return bool
     */
    public function connect(array $serverInfo)
    {
        return true;
    }

    /**
     * 执行SQL语句
     * @link https://wiki.swoole.com/wiki/page/596.html
     *
     * @param string $sql
     * @param double $timeout 超时时间，超时的话会断开MySQL连接，0表示不设置超时时间。
     * @return array|bool  超时/出错返回 false，否则以数组形式返回查询结果
     */
    public function query($sql, $timeout = 0.0)
    {

    }

}