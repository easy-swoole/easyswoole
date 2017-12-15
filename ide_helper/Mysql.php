<?php
/**
 * swoole_mysql
 * Async mysql client
 * User: lushuncheng<admin@lushuncheng.com>
 * Date: 2017/3/1
 * Time: 18:17
 * @link https://github.com/lscgzwd
 * @copyright Copyright (c) 2017 Lu Shun Cheng (https://github.com/lscgzwd)
 * @licence http://www.apache.org/licenses/LICENSE-2.0
 * @author Lu Shun Cheng (lscgzwd@gmail.com)
 *
 *
 * @package Swoole
 * @version 1.0
 */

namespace Swoole;
/**
 * Class Mysql
 * @package Swoole
 * @property $connect_errno
 * @property $connect_error
 * @property $errno
 * @property $error
 * @property $insert_id
 * @property $affected_rows
 */
class Mysql
{
    /**
     * @var string the connect error number
     */
    public $connect_errno;
    /**
     * @var string the connect error information
     */
    public $connect_error;
    /**
     * @var string the error number when query fail
     */
    public $errno;
    /**
     * @var string the error information when query fail
     */
    public $error;
    /**
     * @var int the last insert id when query execute success
     */
    public $insert_id;
    /**
     * @var int the affected rows when query execute success
     */
    public $affected_rows;

    public function __construct()
    {
    }

    /**
     * set the event callback, current just support close
     * function callback(\Swoole\Mysql $db){}
     * @param string $eventName
     * @param callable $callback
     */
    public function on(string $eventName, callable $callback)
    {

    }

    /**
     * $config = array(
     * 'host' => '192.168.56.102', // the host for mysql server ,support ipv4,ipv6 or unix sock.
     * 'user' => 'test', // mysql user name
     * 'password' => 'test', // password for mysql
     * 'database' => 'test', // the database
     * 'charset' => 'utf8', // choice, if not given, the server charset used
     * );
     * function callback(\Swoole\Mysql $db, bool $result) {
     * }
     * @param array $config
     * @param callable $callback
     * @throws \Swoole\Mysql\Exception $e
     */
    public function connect(array $config, callable $callback)
    {

    }

    /**
     * use mysqlnd to escape  the string
     * use --enable-mysqlnd when compile
     * @param string $str
     * @return string
     */
    public function escape(string $str): string
    {

    }

    /**
     * function callback(\Swoole\Mysql $link, mixed $result) {}
     * when execute fail, the result return false, you can use $link->error and $link->errno to get
     * the error information.
     * success:
     * if query result sql, the result is the query result
     * otherwise the result is true, you can use $link->insert_id, $link->affected_rows
     * @param string $sql
     * @param callable $callback
     */
    public function query(string $sql, callable $callback)
    {

    }

    /**
     * start a new transaction
     * one link only one transaction, if already exist, then exception
     * function callback(\Swoole\Mysql $link, mixed $result) {}
     * @param callable $callback
     * @throws \Swoole\Mysql\Exception $e
     */
    public function begin(callable $callback)
    {

    }
    /**
     * commit transaction
     * if not exist, then exception
     * function callback(\Swoole\Mysql $link, mixed $result) {}
     * @param callable $callback
     * @throws \Swoole\Mysql\Exception $e
     */
    public function commit(callable $callback)
    {

    }
    /**
     * rollback transaction
     * if not exist, then exception
     * function callback(\Swoole\Mysql $link, mixed $result) {}
     * @param callable $callback
     * @throws \Swoole\Mysql\Exception $e
     */
    public function rollback(callable $callback)
    {

    }

    /**
     * close the connection
     */
    public function close()
    {

    }
}
