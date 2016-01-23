<?php
/**
 * Author: Arne Gockeln, WebSDK
 * Date: 30.08.15
 */

namespace WebSDK;

class Database
{
    const VERSION = "1.0";
    /**
     * Singleton
     * @var null
     */
    static private $instance = null;
    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * MySQLi connection
     * @var \mysqli
     */
    private $connection;
    /**
     * Current query resource
     * @var null
     */
    private $query = null;
    /**
     * The sql string
     * @var string
     */
    private $sqlString;
    /**
     * Current num rows
     * @var int
     */
    private $numRows = 0;
    /**
     * Last insert id
     * @var int
     */
    private $last_insert_id = 0;
    /**
     * Affected rows from last query
     * @var int
     */
    private $affected_rows = 0;
    /**
     * List errors
     * @var array
     */
    private $errors = array();

    /**
     * Constructor
     * @throws \Exception
     */
    public function __construct(){
        $this->connect();
    }

    /**
     * Send sql query to database
     * @param $sql
     * @return $this
     * @throws \Exception
     */
    public function query($sql) {
        try {
            if(strlen($sql) <= 0) {
                array_push($this->errors, _('Database: SQL string is empty!'));
                throw new \Exception(_('Database: SQL string is empty!'));
            }

            // set sql string
            $this->setSqlString($sql);

            // connect to database
            if(is_null($this->getConnection()) || $this->getConnection() === false){
                $this->connect();
            }

            // query
            $this->setQuery($this->getConnection()->query($sql));
            if(!$this->getQuery()){
                array_push($this->errors, $this->getConnection()->error);
                throw new \Exception($this->getConnection()->error);
            }

            // last insert id
            if($this->getConnection()->insert_id > 0){
                $this->setLastInsertId($this->getConnection()->insert_id);
            }

            // affected rows
            $this->setAffectedRows($this->getConnection()->affected_rows);
        } catch(\Exception $e){
            print_r($e->getMessage());
        }


        return $this;
    }

    /**
     * Fetch a single row from query
     * @param bool|false $assoc
     * @return array|null|object
     */
    public function fetchRow($assoc = false){
        $row = null;
        switch($assoc){
            case true:
                $row = $this->getQuery()->fetch_assoc();
                break;
            case false:
            default:
                $row = $this->getQuery()->fetch_object();
                break;
        }

        return $row;
    }

    /**
     * Fetch a list from query
     * @param bool|false $assoc
     * @return array
     */
    public function fetchList($assoc = false){
        $list = array();
        switch($assoc){
            case true:
                while($row = $this->getQuery()->fetch_assoc()){
                    $list[] = $row;
                }
                break;
            case false:
            default:
                while($row = $this->getQuery()->fetch_object()){
                    $list[] = $row;
                }
                break;
        }

        return $list;
    }


    /**
     * Parse sql query string with column/value array
     * @param string $sql
     * @param array $columns
     * @return string
     */
    public function getParsedSql($sql = '', $columns = array()){
        $field_string = '';
        $sqlstring = '';

        $type = 'SELECT';
        $haystack = substr($sql, 0, 6);
        if(stripos($haystack, 'SELECT') !== false){
            $type = 'SELECT';
        } else if(stripos($haystack, 'UPDATE') !== false || stripos($sql, 'ON DUPLICATE KEY UPDATE') !== false){
            $type = 'UPDATE';
        } else if(stripos($haystack, 'INSERT') !== false){
            $type = 'INSERT';
        }

        switch($type){
            case 'SELECT':
                foreach($columns as $key => $value){
                    $field_string .= "`$key`,";
                }

                $sqlstring = sprintf($sql, substr($field_string, 0, -1));
                break;
            case 'UPDATE':
                foreach($columns as $key => $value){
                    if(is_array($value)){
                        if(array_key_exists('escape', $value)){
                            $field_string .= "`$key` = " . $value['value'] . ",";
                        }
                        continue;
                    }

                    $field_string .= "`$key` = '" . $this->getEscapedString($value) . "',";
                }

                $sqlstring = sprintf($sql, substr($field_string, 0, -1));
                break;
            case 'INSERT':
                $value_string = '';
                foreach($columns as $key => $value){
                    if(is_array($value)){
                        if(array_key_exists('escape', $value)){
                            $field_string .= "`$key`,";
                            $value_string .= $value['value'] . ",";
                        }
                        continue;
                    }

                    $field_string .= "`$key`,";
                    $value_string .= "'" . $this->getEscapedString($value) . "',";
                }

                $sqlstring = sprintf($sql, substr($field_string, 0, -1), substr($value_string, 0, -1));
                break;
        }

        return $sqlstring;
    }

    /**
     * Alias for mysqli_real_escape_string()
     * @param $value
     * @return mixed
     */
    public function getEscapedString($value){
        return $this->getConnection()->real_escape_string($value);
    }

    /**
     * Debug
     */
    public function debug(){
        $info = array(
            'sql' => $this->getSqlString(),
            'host_info' => $this->getConnection()->host_info
        );
        debug($info);
    }

    /**
     * Connect to mysql database
     * @throws Exception
     */
    private function connect(){
        $connection = new \mysqli(CFG_DB_HOST, CFG_DB_USER, CFG_DB_PASSWORD, CFG_DB_DATABASE);

        if($connection->connect_errno){
            array_push($this->errors, $connection->connect_error);
            throw new \Exception($connection->connect_error);
        }

        $connection->query("set names utf8;");

        $this->setConnection($connection);
    }

    /**
     * Do we have affected rows?
     * @return bool
     */
    public function hasAffectedRows(){
        return $this->getAffectedRows() > 0;
    }

    /**
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->affected_rows;
    }

    /**
     * @param int $affected_rows
     */
    public function setAffectedRows($affected_rows)
    {
        $this->affected_rows = $affected_rows;
    }

    /**
     * Do we have rows?
     * @return bool
     */
    public function hasRows(){
        return $this->getNumRows() > 0;
    }

    /**
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->last_insert_id;
    }

    /**
     * @param int $last_insert_id
     */
    public function setLastInsertId($last_insert_id)
    {
        $this->last_insert_id = $last_insert_id;
    }

    /**
     * @return int
     */
    public function getNumRows()
    {
        if($this->getQuery()){
            return $this->getQuery()->num_rows;
        }
        return $this->numRows;
    }

    /**
     * @param int $numRows
     */
    public function setNumRows($numRows)
    {
        $this->numRows = $numRows;
    }

    /**
     * @return null
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param null $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get mysqli connection
     * @return \mysqli
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param \mysqli $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getSqlString()
    {
        return $this->sqlString;
    }

    /**
     * @param string $sqlString
     */
    public function setSqlString($sqlString)
    {
        $this->sqlString = $sqlString;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        if(array_key_exists('host', $settings) || array_key_exists('username', $settings) || array_key_exists('password', $settings) || array_key_exists('port', $settings)){
            $this->settings = $settings;
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * Check if we have some errors
     * @return bool
     */
    public function hasErrors(){
        return count($this->getErrors()) > 0;
    }

    /**
     * Clear Error List
     */
    public function clearErrors(){
        $this->setErrors(array());
    }


    /**
     * Return array or object of a row by ID
     * @param string $table
     * @param int $id
     * @param bool|true $assoc
     * @return array|null|object
     * @throws \Exception
     */
    public static function getRowOf($table, $id, $assoc = true){
        if(is_empty($table) || !isSecureString($table)){
            throw new \Exception(_('Database: TABLE is empty!'));
        }
        if(is_empty($id) || !isSecureString($id)){
            throw new \Exception(_('Database: ID is empty!'));
        }

        $mysql = self::getInstance();
        $sql = "SELECT * FROM " . $mysql->getEscapedString($table) . " WHERE id = '" . $mysql->getEscapedString($id) . "'";
        return $mysql->query($sql)->fetchRow($assoc);
    }

    /**
     * @param string $table
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public static function deleteRowWithID($table, $id){
        if(is_empty($table) || !isSecureString($table)){
            throw new \Exception(_('Database: TABLE is empty!'));
        }
        if(is_empty($id) || !isSecureString($id)){
            throw new \Exception(_('Database: ID is empty!'));
        }

        $mysql = self::getInstance();
        $sql = "DELETE FROM " . $mysql->getEscapedString($table) . " WHERE id = '" . $mysql->getEscapedString($id) . "'";
        $mysql->query($sql);
        return $mysql->hasAffectedRows();
    }

    /**
     * Update a single row by id
     * @param string $table
     * @param array $valuesPreparedForDB use prepareForDB()
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public static function updateRowWithID($table, $valuesPreparedForDB, $id){
        if(is_empty($table) || !isSecureString($table)){
            throw new \Exception(_('Database: TABLE is empty!'));
        }
        if(is_empty($id) || !isSecureString($id)){
            throw new \Exception(_('Database: ID is empty!'));
        }
        if(is_empty($valuesPreparedForDB)){
            throw new \Exception(_('Database: VALUES are empty!'));
        }

        $mysql = self::getInstance();
        $mysql->query(
            $mysql->getParsedSql("UPDATE " . $mysql->getEscapedString($table) . " SET %s WHERE id = '" . $mysql->getEscapedString($id) . "'", $valuesPreparedForDB)
        );

        if($mysql->hasAffectedRows() || !$mysql->hasErrors()){
            return true;
        }
        return false;
    }

    /**
     * Insert a row into table
     * @param string $table
     * @param array $valuesPrepardForDB
     * @return int last insert id
     * @throws \Exception
     */
    public static function insertRow($table, $valuesPrepardForDB){
        if(is_empty($table) || !isSecureString($table)){
            throw new \Exception(_('Database: TABLE is empty!'));
        }
        if(is_empty($valuesPrepardForDB)){
            throw new \Exception(_('Database: VALUES are empty!'));
        }

        $mysql = self::getInstance();
        $mysql->query(
            $mysql->getParsedSql("INSERT INTO " . $mysql->getEscapedString($table) . "(%s) VALUES(%s)", $valuesPrepardForDB)
        );

        if(!$mysql->hasErrors()){
            return $mysql->getLastInsertId();
        }
    }
}