<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/9
 * Time: 下午2:32
 */

namespace EasySwoole\Core\Swoole\Coroutine\Client;
use \Swoole\Coroutine\MySQL as CoroutineMySQL;

/*
 * 本类改编自https://github.com/joshcam/PHP-MySQLi-Database-Class
 */
class Mysql
{
    protected $host = null, $username = null, $password = null, $db = null, $port = 3306;

    protected $client = null;
    protected $reConnectTimes = 0;
    protected $autoReConnect = 0;
    protected $isSubQuery = false;
    protected $defConnectionName;
    protected $connectionsSettings = [];
    protected $count;

    private $_where = [];
    private $_tableName;
    private $_query;
    private $_queryOptions = [];
    private $_bindParams = [];
    private $_updateColumns;
    private $_lastInsertId;
    private $_forUpdate;
    private $_lockInShareMode;
    private $_lastQuery;
    private $_having;
    private $_mapKey;
    private $_nestJoin;
    private $_orderBy;
    private $_groupBy;
    private $_joinAnd;
    private $_join;
    private $_stmtError;
    private $_stmtErrno;
    private $_tableLockMethod = "READ";
    private $_alias = null;


    public function __construct($host = null, $username = null, $password = null, $db = null, $port = 3306,int $autoReConnect = 3)
    {
        $isSubQuery = false;
        if (is_array($host)) {
            foreach ($host as $key => $val) {
                $$key = $val;
            }
        }
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->db = $db;
        $this->port = $port;
        $this->autoReConnect = $autoReConnect;

        if ($isSubQuery) {
            $this->isSubQuery = true;
            $this->_alias = $host;
            return;
        }

        $this->client = new CoroutineMySQL();
        $this->connect();
    }


    function connect()
    {
        if(!$this->client->connected){
            $this->client->connect([
                'host' => $this->host,
                'port' => $this->port,
                'user' => $this->username,
                'password' => $this->password,
                'database' => $this->db,
            ]);
            if($this->client->connected){
                $this->reConnectTimes = 0;
            }else{
                $this->reConnectTimes++;
            }
        }
    }

    function disConnect()
    {
        $this->client->close();
    }

    function client():CoroutineMySQL
    {
        return $this->client;
    }

    public function where($whereProp, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND'):Mysql
    {
        if (is_array($whereValue) && ($key = key($whereValue)) != "0") {
            $operator = $key;
            $whereValue = $whereValue[$key];
        }

        if (count($this->_where) == 0) {
            $cond = '';
        }

        $this->_where[] = array($cond, $whereProp, $operator, $whereValue);
        return $this;
    }

    public function orWhere($whereProp, $whereValue = 'DBNULL', $operator = '='):Mysql
    {
        return $this->where($whereProp, $whereValue, $operator, 'OR');
    }

    public function get($tableName, $numRows = null, $columns = '*')
    {
        $this->_tableName = $tableName;

        if (empty($columns)) {
            $columns = '*';
        }

        $column = is_array($columns) ? implode(', ', $columns) : $columns;


        $this->_query = 'SELECT ' . implode(' ', $this->_queryOptions) . ' ' .
            $column . " FROM " . $this->_tableName;

        $stmt = $this->_buildQuery($numRows);

        if ($this->isSubQuery) {
            return $this;
        }

        $res = $this->exec($stmt);
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $this->reset();
        return $res;
    }

    public function getOne($tableName, $columns = '*')
    {
        $res = $this->get($tableName, 1, $columns);

        if ($res instanceof Mysql) {
            return $res;
        } elseif (is_array($res) && isset($res[0])) {
            return $res[0];
        } elseif ($res) {
            return $res;
        }

        return null;
    }

    public function insert($tableName, $insertData)
    {
        return $this->_buildInsert($tableName, $insertData, 'INSERT');
    }

    public function delete($tableName, $numRows = null)
    {
        if ($this->isSubQuery) {
            return;
        }

        $table =  $tableName;

        if (count($this->_join)) {
            $this->_query = "DELETE " . preg_replace('/.* (.*)/', '$1', $table) . " FROM " . $table;
        } else {
            $this->_query = "DELETE FROM " . $table;
        }

        $stmt = $this->_buildQuery($numRows);
        $this->exec($stmt);
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $this->reset();

        return ($stmt->affected_rows > -1);	//	affected_rows returns 0 if nothing matched where statement, or required updating, -1 if error
    }

    public function update($tableName, $tableData, $numRows = null)
    {
        if ($this->isSubQuery) {
            return;
        }

        $this->_query = "UPDATE " . $tableName;

        $stmt = $this->_buildQuery($numRows, $tableData);
        $status = $this->exec($stmt);
        $this->reset();
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $this->count = $stmt->affected_rows;

        return $status;
    }

    public function tableExists($tables)
    {
        $tables = !is_array($tables) ? Array($tables) : $tables;
        $count = count($tables);
        if ($count == 0) {
            return false;
        }

        foreach ($tables as $i => $value)
            $tables[$i] =  $value;
        $this->where('table_schema', $this->db);
        $this->where('table_name', $tables, 'in');
        $ret = $this->get('information_schema.tables', $count);
        if(is_array($ret) && $count == count($ret)){
            return true;
        }else{
            return false;
        }
    }

    public function inc($num = 1)
    {
        if (!is_numeric($num)) {
            throw new \Exception('Argument supplied to inc must be a number');
        }
        return array("[I]" => "+" . $num);
    }

    public function dec($num = 1)
    {
        if (!is_numeric($num)) {
            throw new \Exception('Argument supplied to dec must be a number');
        }
        return array("[I]" => "-" . $num);
    }

    public function join($joinTable, $joinCondition, $joinType = '')
    {
        $allowedTypes = array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER', 'NATURAL');
        $joinType = strtoupper(trim($joinType));

        if ($joinType && !in_array($joinType, $allowedTypes)) {
            throw new \Exception('Wrong JOIN type: ' . $joinType);
        }

        $this->_join[] = Array($joinType, $joinTable, $joinCondition);

        return $this;
    }

    public function rawQuery($query, array $bindParams = [])
    {
        $this->_bindParams = $bindParams;
        $this->_query = $query;
        $stmt = $this->_prepareQuery();
        $res = $this->exec($stmt);
        $this->count = $stmt->affected_rows;
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $this->_lastQuery = $this->replacePlaceHolders($this->_query, $bindParams);
        $this->reset();
        return $res;
    }

    protected function _buildQuery($numRows = null, $tableData = null)
    {
        $this->_buildJoin();
        $this->_buildInsertQuery($tableData);
        $this->_buildCondition('WHERE', $this->_where);
        $this->_buildGroupBy();
        $this->_buildCondition('HAVING', $this->_having);
        $this->_buildOrderBy();
        $this->_buildLimit($numRows);
        $this->_buildOnDuplicate($tableData);

        if ($this->_forUpdate) {
            $this->_query .= ' FOR UPDATE';
        }
        if ($this->_lockInShareMode) {
            $this->_query .= ' LOCK IN SHARE MODE';
        }

        $this->_lastQuery = $this->replacePlaceHolders($this->_query, $this->_bindParams);

        if ($this->isSubQuery) {
            return;
        }

        // Prepare query
        $stmt = $this->_prepareQuery();
        return $stmt;
    }

    protected function exec($stmt)
    {
        if(!$this->client->connected && $this->reConnectTimes === 0){
            $this->connect();
            return $this->exec($stmt);
        }else if($this->client->connected){
            if (!empty($this->_bindParams)) {
                $data = $this->_bindParams;
            }else{
                $data = [];
            }
            return $stmt->execute($data);
        }else{
            return null;
        }
    }

    protected function _buildJoin () {
        if (empty ($this->_join))
            return;

        foreach ($this->_join as $data) {
            list ($joinType,  $joinTable, $joinCondition) = $data;

            if (is_object ($joinTable))
                $joinStr = $this->_buildPair ("", $joinTable);
            else
                $joinStr = $joinTable;

            $this->_query .= " " . $joinType. " JOIN " . $joinStr .
                (false !== stripos($joinCondition, 'using') ? " " : " on ")
                . $joinCondition;

            // Add join and query
            if (!empty($this->_joinAnd) && isset($this->_joinAnd[$joinStr])) {
                foreach($this->_joinAnd[$joinStr] as $join_and_cond) {
                    list ($concat, $varName, $operator, $val) = $join_and_cond;
                    $this->_query .= " " . $concat ." " . $varName;
                    $this->conditionToSql($operator, $val);
                }
            }
        }
    }

    protected function _buildPair($operator,$value)
    {
        if (!is_object($value)) {
            $this->_bindParam($value);
            return ' ' . $operator . ' ? ';
        }

        $subQuery = $value->getSubQuery();
        $this->_bindParams($subQuery['params']);

        return " " . $operator . " (" . $subQuery['query'] . ") " . $subQuery['alias'];
    }

    protected function _bindParam($value)
    {
        array_push($this->_bindParams, $value);
    }

    protected function _bindParams($values)
    {
        foreach ($values as $value) {
            $this->_bindParam($value);
        }
    }

    protected function _determineType($item)
    {
        switch (gettype($item)) {
            case 'NULL':
            case 'string':
                return 's';
                break;

            case 'boolean':
            case 'integer':
                return 'i';
                break;

            case 'blob':
                return 'b';
                break;

            case 'double':
                return 'd';
                break;
        }
        return '';
    }

    protected function conditionToSql($operator, $val) {
        switch (strtolower ($operator)) {
            case 'not in':
            case 'in':
                $comparison = ' ' . $operator. ' (';
                if (is_object ($val)) {
                    $comparison .= $this->_buildPair ("", $val);
                } else {
                    foreach ($val as $v) {
                        $comparison .= ' ?,';
                        $this->_bindParam ($v);
                    }
                }
                $this->_query .= rtrim($comparison, ',').' ) ';
                break;
            case 'not between':
            case 'between':
                $this->_query .= " $operator ? AND ? ";
                $this->_bindParams ($val);
                break;
            case 'not exists':
            case 'exists':
                $this->_query.= $operator . $this->_buildPair ("", $val);
                break;
            default:
                if (is_array ($val))
                    $this->_bindParams ($val);
                else if ($val === null)
                    $this->_query .= $operator . " NULL";
                else if ($val != 'DBNULL' || $val == '0')
                    $this->_query .= $this->_buildPair ($operator, $val);
        }
    }

    public function getSubQuery()
    {
        if (!$this->isSubQuery) {
            return null;
        }

//        array_shift($this->_bindParams);
        $val = Array('query' => $this->_query,
            'params' => $this->_bindParams,
            'alias' => $this->_alias
        );
        $this->reset();
        return $val;
    }

    public static function subQuery($subQueryAlias = "")
    {
        return new self(array('host' => $subQueryAlias, 'isSubQuery' => true));
    }

    protected function _buildInsertQuery($tableData)
    {
        if (!is_array($tableData)) {
            return;
        }

        $isInsert = preg_match('/^[INSERT|REPLACE]/', $this->_query);
        $dataColumns = array_keys($tableData);
        if ($isInsert) {
            if (isset ($dataColumns[0]))
                $this->_query .= ' (`' . implode($dataColumns, '`, `') . '`) ';
            $this->_query .= ' VALUES (';
        } else {
            $this->_query .= " SET ";
        }

        $this->_buildDataPairs($tableData, $dataColumns, $isInsert);

        if ($isInsert) {
            $this->_query .= ')';
        }
    }

    public function _buildDataPairs($tableData, $tableColumns, $isInsert)
    {
        foreach ($tableColumns as $column) {
            $value = $tableData[$column];

            if (!$isInsert) {
                if(strpos($column,'.')===false) {
                    $this->_query .= "`" . $column . "` = ";
                } else {
                    $this->_query .= str_replace('.','.`',$column) . "` = ";
                }
            }

            // Subquery value
            if ($value instanceof Mysql) {
                $this->_query .= $this->_buildPair("", $value) . ", ";
                continue;
            }

            // Simple value
            if (!is_array($value)) {
                $this->_bindParam($value);
                $this->_query .= '?, ';
                continue;
            }

            // Function value
            $key = key($value);
            $val = $value[$key];
            switch ($key) {
                case '[I]':
                    $this->_query .= $column . $val . ", ";
                    break;
                case '[F]':
                    $this->_query .= $val[0] . ", ";
                    if (!empty($val[1])) {
                        $this->_bindParams($val[1]);
                    }
                    break;
                case '[N]':
                    if ($val == null) {
                        $this->_query .= "!" . $column . ", ";
                    } else {
                        $this->_query .= "!" . $val . ", ";
                    }
                    break;
                default:
                    throw new Exception("Wrong operation");
            }
        }
        $this->_query = rtrim($this->_query, ', ');
    }
    protected function _buildCondition($operator, &$conditions)
    {
        if (empty($conditions)) {
            return;
        }

        $this->_query .= ' ' . $operator;

        foreach ($conditions as $cond) {
            list ($concat, $varName, $operator, $val) = $cond;
            $this->_query .= " " . $concat . " " . $varName;

            switch (strtolower($operator)) {
                case 'not in':
                case 'in':
                    $comparison = ' ' . $operator . ' (';
                    if (is_object($val)) {
                        $comparison .= $this->_buildPair("", $val);
                    } else {
                        foreach ($val as $v) {
                            $comparison .= ' ?,';
                            $this->_bindParam($v);
                        }
                    }
                    $this->_query .= rtrim($comparison, ',') . ' ) ';
                    break;
                case 'not between':
                case 'between':
                    $this->_query .= " $operator ? AND ? ";
                    $this->_bindParams($val);
                    break;
                case 'not exists':
                case 'exists':
                    $this->_query.= $operator . $this->_buildPair("", $val);
                    break;
                default:
                    if (is_array($val)) {
                        $this->_bindParams($val);
                    } elseif ($val === null) {
                        $this->_query .= ' ' . $operator . " NULL";
                    } elseif ($val != 'DBNULL' || $val == '0') {
                        $this->_query .= $this->_buildPair($operator, $val);
                    }
            }
        }
    }

    protected function _buildGroupBy()
    {
        if (empty($this->_groupBy)) {
            return;
        }

        $this->_query .= " GROUP BY ";

        foreach ($this->_groupBy as $key => $value) {
            $this->_query .= $value . ", ";
        }

        $this->_query = rtrim($this->_query, ', ') . " ";
    }

    protected function _buildOrderBy()
    {
        if (empty($this->_orderBy)) {
            return;
        }

        $this->_query .= " ORDER BY ";
        foreach ($this->_orderBy as $prop => $value) {
            if (strtolower(str_replace(" ", "", $prop)) == 'rand()') {
                $this->_query .= "rand(), ";
            } else {
                $this->_query .= $prop . " " . $value . ", ";
            }
        }

        $this->_query = rtrim($this->_query, ', ') . " ";
    }

    protected function _buildLimit($numRows)
    {
        if (!isset($numRows)) {
            return;
        }

        if (is_array($numRows)) {
            $this->_query .= ' LIMIT ' . (int) $numRows[0] . ', ' . (int) $numRows[1];
        } else {
            $this->_query .= ' LIMIT ' . (int) $numRows;
        }
    }

    protected function _buildOnDuplicate($tableData)
    {
        if (is_array($this->_updateColumns) && !empty($this->_updateColumns)) {
            $this->_query .= " ON DUPLICATE KEY UPDATE ";
            if ($this->_lastInsertId) {
                $this->_query .= $this->_lastInsertId . "=LAST_INSERT_ID (" . $this->_lastInsertId . "), ";
            }

            foreach ($this->_updateColumns as $key => $val) {
                // skip all params without a value
                if (is_numeric($key)) {
                    $this->_updateColumns[$val] = '';
                    unset($this->_updateColumns[$key]);
                } else {
                    $tableData[$key] = $val;
                }
            }
            $this->_buildDataPairs($tableData, array_keys($this->_updateColumns), false);
        }
    }

    protected function replacePlaceHolders($str, $vals)
    {
        $i = 0;
        $newStr = "";

        if (empty($vals)) {
            return $str;
        }

        while ($pos = strpos($str, "?")) {
            $val = $vals[$i++];
            if (is_object($val)) {
                $val = '[object]';
            }
            if ($val === null) {
                $val = 'NULL';
            }
            $newStr .= substr($str, 0, $pos) . "'" . $val . "'";
            $str = substr($str, $pos + 1);
        }
        $newStr .= $str;
        return $newStr;
    }

    protected function _prepareQuery()
    {
        if(!$this->client->connected && $this->reConnectTimes === 0){
            $this->connect();
            return $this->_prepareQuery();
        }else if($this->client->connected){
            $res =  $this->client()->prepare($this->_query);
            if($res){
                return $res;
            }
        }
        $error = $this->client()->error;
        $query = $this->_query;
        $errno = $this->client()->errno;
        $this->reset();
        throw new \Exception(sprintf('%s query: %s', $error, $query), $errno);
    }

    protected function reset()
    {
        $this->_where = array();
        $this->_having = array();
        $this->_join = array();
        $this->_joinAnd = array();
        $this->_orderBy = array();
        $this->_groupBy = array();
        $this->_bindParams = array();
        $this->_query = null;
        $this->_queryOptions = array();
        $this->_nestJoin = false;
        $this->_forUpdate = false;
        $this->_lockInShareMode = false;
        $this->_tableName = '';
        $this->_lastInsertId = null;
        $this->_updateColumns = null;
        $this->_mapKey = null;
        $this->defConnectionName = 'default';
        $this->reConnectTimes = 0;
        $this->_alias = null;
        return $this;
    }

    protected function refValues(array &$arr)
    {
        if (strnatcmp(phpversion(), '5.3') >= 0) {
            $refs = array();
            foreach ($arr as $key => $value) {
                $refs[$key] = & $arr[$key];
            }
            return $refs;
        }
        return $arr;
    }

    private function _buildInsert($tableName, $insertData, $operation)
    {
        if ($this->isSubQuery) {
            return;
        }

        $this->_query = $operation . " " . implode(' ', $this->_queryOptions) . " INTO " . $tableName;
        $stmt = $this->_buildQuery(null, $insertData);
        $status = $this->exec($stmt);
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $haveOnDuplicate = !empty ($this->_updateColumns);
        $this->reset();
        $this->count = $stmt->affected_rows;

        if ($stmt->affected_rows < 1) {
            // in case of onDuplicate() usage, if no rows were inserted
            if ($status && $haveOnDuplicate) {
                return true;
            }
            return false;
        }

        if ($stmt->insert_id > 0) {
            return $stmt->insert_id;
        }

        return true;
    }

    public function getInsertId()
    {
        return $this->client()->insert_id;
    }

    public function getLastQuery()
    {
        return $this->_lastQuery;
    }

    /**
     * Method returns mysql error
     *
     * @return string
     */
    public function getLastError()
    {
        return trim($this->_stmtError . " " . $this->client->error);
    }

    public function getLastErrno () {
        return $this->_stmtErrno;
    }

    public function orderBy($orderByField, $orderbyDirection = "DESC", $customFieldsOrRegExp = null)
    {
        $allowedDirection = Array("ASC", "DESC");
        $orderbyDirection = strtoupper(trim($orderbyDirection));
        $orderByField = preg_replace("/[^ -a-z0-9\.\(\),_`\*\'\"]+/i", '', $orderByField);

        $orderByField = preg_replace('/(\`)([`a-zA-Z0-9_]*\.)/', '\1' . '\2', $orderByField);

        if (empty($orderbyDirection) || !in_array($orderbyDirection, $allowedDirection)) {
            throw new \Exception('Wrong order direction: ' . $orderbyDirection);
        }

        if (is_array($customFieldsOrRegExp)) {
            foreach ($customFieldsOrRegExp as $key => $value) {
                $customFieldsOrRegExp[$key] = preg_replace("/[^-a-z0-9\.\(\),_` ]+/i", '', $value);
            }
            $orderByField = 'FIELD (' . $orderByField . ', "' . implode('","', $customFieldsOrRegExp) . '")';
        }elseif(is_string($customFieldsOrRegExp)){
            $orderByField = $orderByField . " REGEXP '" . $customFieldsOrRegExp . "'";
        }elseif($customFieldsOrRegExp !== null){
            throw new \Exception('Wrong custom field or Regular Expression: ' . $customFieldsOrRegExp);
        }

        $this->_orderBy[$orderByField] = $orderbyDirection;
        return $this;
    }

    public function groupBy($groupByField)
    {
        $groupByField = preg_replace("/[^-a-z0-9\.\(\),_\* <>=!]+/i", '', $groupByField);

        $this->_groupBy[] = $groupByField;
        return $this;
    }
    //swoole mysql client暂时不支持锁表
//    public function lock($table)
//    {
//        // Main Query
//        $this->_query = "LOCK TABLES";
//
//        // Is the table an array?
//        if(gettype($table) == "array") {
//            // Loop trough it and attach it to the query
//            foreach($table as $key => $value) {
//                if(gettype($value) == "string") {
//                    if($key > 0) {
//                        $this->_query .= ",";
//                    }
//                    $this->_query .= " ".$value." ".$this->_tableLockMethod;
//                }
//            }
//        }
//        else{
//            // Build the query
//            $this->_query = "LOCK TABLES ".$table." ".$this->_tableLockMethod;
//        }
//
//        // Exceute the query unprepared because LOCK only works with unprepared statements.
//        $result = $this->rawQuery($this->_query);
//        $errno  = $this->client()->errno;
//
//        // Reset the query
//        $this->reset();
//
//        // Are there rows modified?
//        if($result) {
//            return true;
//        }
//        // Something went wrong
//        else {
//            throw new \Exception("Locking of table ".$table." failed", $errno);
//        }
//
//        // Return the success value
//        return false;
//    }
//
//
//    public function unlock()
//    {
//        // Build the query
//        $this->_query = "UNLOCK TABLES";
//
//        // Exceute the query unprepared because UNLOCK and LOCK only works with unprepared statements.
//        $result = $this->rawQuery($this->_query);
//        $errno  = $this->client()->errno;
//
//        // Reset the query
//        $this->reset();
//
//        // Are there rows modified?
//        if($result) {
//            // return self
//            return $this;
//        }
//        // Something went wrong
//        else {
//            throw new \Exception("Unlocking of tables failed", $errno);
//        }
//
//        return $this;
//    }



}