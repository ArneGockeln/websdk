<?php
class mysqlQueryType {

  const SELECT = 0;
  const INSERT = 1;
  const UPDATE = 2;
  const DELETE = 3;

}

class mysqlFetchMethod {

  const OBJECT_METHOD = 0;
  const ARRAY_METHOD = 1;

}

/**
 * '=', '>', '<', '<>', '!=', 'LIKE', 'IN', 'FIND_IN_SET'
 */
class mysqlRestrictionType {
  const EQUALS = '=';
  const GREATER_THEN = '>';
  const SMALLER_THEN = '<';
  const GREATER_OR_SMALLER_THEN = '<>';
  const UNEQUAL = '!=';
  const LIKE = 'LIKE';
  const IN = 'IN';
  const FIND_IN_SET = 'FIND_IN_SET';
}

class mysqlDatabase {
  /**
   * Configuration
   */

  /**
   * Intern 
   */
  private $columns = array();
  private $table = '';
  private $restrictions = array();
  private $result = array();
  private $link = false;
  public $last_insert_id = 0;
  public $sqlString = '';
  private $fetchMethod = mysqlFetchMethod::OBJECT_METHOD;
  private $isCustomQuery = false;
  private $order = array();

  public function __construct($table = false) {
    $this->reset();
    
    if($table !== false){
      $this->setTable($table);
    }
  }

  /**
   * Resets all private vars
   */
  public function reset() {
    $this->columns = array();
    $this->table = '';
    $this->restrictions = array();
    $this->sqlString = '';
    $this->result = array();
    $this->isCustomQuery = false;
    $this->fetchMethod = mysqlFetchMethod::OBJECT_METHOD;
    $this->last_insert_id = 0;
    $this->order = array();
  }

  /**
   * Connect to database
   * @return mysql ressource
   */
  private function connect() {
    $this->link = mysql_connect(CFG_DB_HOST, CFG_DB_USER, CFG_DB_PASSWORD, $this->link);
    if ($this->link)
      mysql_select_db(CFG_DB_DATABASE);
    mysql_query("set names utf8;");
    return $this->link;
  }

  /*
   * Prepare SQL String
   */

  private function prepareSQL($queryType) {
    if ($this->isCustomQuery)
      return;

    if (strlen($this->table) == 0)
      throw new Exception(ALERT_ERR_DB_NO_TABLE_SET);

    $sql = '';
    switch ($queryType) {
      case mysqlQueryType::SELECT:
        if (!$this->hasColumns())
          throw new Exception(ALERT_ERR_DB_NO_COLUMNS_SET);

        $sql = "SELECT ";
        
        /**
         * SELECTION
         */
        if(array_key_exists('*', $this->columns)){
          $sql .= "*";
        } else {
          foreach ($this->columns as $column => $value) {
            $sql .= "`" . $column . "`,";
          }
          $sql = substr($sql, 0, -1);
        }

        $sql .= " FROM " . $this->table;

        /**
         * RESTRICTIONS
         */
        if ($this->hasRestrictions()) {
          $sql .= " WHERE ";
          $lastConnector = '';
          foreach ($this->restrictions as $int => $restriction) {
            
            $value = $restriction['value'];
            
            // FIND_IN_SET
            if($restriction['operator'] == 'FIND_IN_SET'){
              $sql .= " FIND_IN_SET(" . ($restriction['escapeValue'] ? "'" : "") . (is_resource($this->link) ? mysql_real_escape_string($value, $this->link) : $value) . ($restriction['escapeValue'] ? "'" : "") . ", " . $restriction['column'] . ") " . $restriction['connector'];
            } else {
              // OTHER RESTRICTIONS
              if (substr($value, 0, 1) != "'") {
                $value = ($restriction['escapeValue'] ? "'" : "") . (is_resource($this->link) ? mysql_real_escape_string($value, $this->link) : $value) . ($restriction['escapeValue'] ? "'" : "");
              }
              $sql .= " `" . $restriction['column'] . "` " . $restriction['operator'] . " " . ($restriction['operator'] == 'IN' ? '(' : '') . $value . ($restriction['operator'] == 'IN' ? ')' : '') . " " . $restriction['connector'];
            }
            $lastConnector = $restriction['connector'];
          }
          $sql = substr($sql, 0, (strlen($lastConnector) * -1));
        }

        /**
         * ORDERING
         */
        if ($this->hasOrder()) {
          $sql .= " ORDER BY ";
          foreach ($this->order as $int => $order) {
            $sql .= $order[0] . ' ' . $order[1] . ',';
          }

          $sql = substr($sql, 0, strlen($sql) - 1);
        }

        break;
      case mysqlQueryType::INSERT:
      case mysqlQueryType::UPDATE:
        if (!$this->hasColumns())
          throw new Exception(_("Keine Spalten gefunden!"));

        $sql_columns = '';
        $sql_values = '';
        $sql_update = '';
        foreach ($this->columns as $column => $value) {
          $sql_columns .= "`" . $column . "`,";
          $sql_values .= $value . ",";
          $sql_update .= "`" . $column . "` = " . $value . ",";
        }
        $sql_columns = substr($sql_columns, 0, -1);
        $sql_values = substr($sql_values, 0, -1);
        $sql_update = substr($sql_update, 0, -1);

        $sql = "INSERT INTO " . $this->table . "(" . $sql_columns . ") VALUES (" . $sql_values . ")";
        $sql .= " ON DUPLICATE KEY UPDATE " . $sql_update;
        break;
      case mysqlQueryType::DELETE:
        if (!$this->hasRestrictions())
          throw new Exception(_('Keine Einschränkungen gefunden!'));

        $sql = "DELETE FROM " . $this->table . " WHERE ";
        $lastConnector = '';
        foreach ($this->restrictions as $int => $restriction) {
          // FIND_IN_SET
          if($restriction['operator'] == 'FIND_IN_SET'){
            $sql .= " FIND_IN_SET(" . ($restriction['escapeValue'] ? "'" : "") . (is_resource($this->link) ? mysql_real_escape_string($value, $this->link) : $value) . ($restriction['escapeValue'] ? "'" : "") . ", " . $restriction['column'] . ") " . $restriction['connector'];
          } else {
            // OTHER RESTRICTIONS
            $value = $restriction['value'];
            if (substr($value, 0, 1) != "'") {
              $value = ($restriction['escapeValue'] ? "'" : "") . (is_resource($this->link) ? mysql_real_escape_string($value, $this->link) : $value) . ($restriction['escapeValue'] ? "'" : "");
            }
            $sql .= " `" . $restriction['column'] . "` " . $restriction['operator'] . " " . ($restriction['operator'] == 'IN' ? '(' : '') . $value . ($restriction['operator'] == 'IN' ? ')' : '') . " " . $restriction['connector'];
          }
          $lastConnector = $restriction['connector'];
        }
        $sql = substr($sql, 0, (strlen($lastConnector) * -1));

        break;
    }
    
    $this->clearSql();
    $this->sqlString = $sql;
  }

  /**
   * Add Column or a list of columns (optional with values)
   * @param mixed $colOrArray string or array(column => value)
   */
  public function setColumn($column, $value = '', $escape = true) {
    if($escape){
      $this->columns[$column] = "'" . (is_resource($this->link) ? mysql_real_escape_string($value, $this->link) : $value) . "'";
    } else {
      $this->columns[$column] = (is_resource($this->link) ? mysql_real_escape_string($value, $this->link) : $value);
    }
    
  }

  /*
   * Add array of columns
   * @param array $columns
   */

  public function setColumns($columns) {
    if (is_array($columns) && count($columns) > 0) {
      foreach ($columns as $column => $value) {

        if (is_int($column)) { // if this is a list for select
          $this->setColumn($value);
        } else { // this is a list for insert, update
          $this->setColumn($column, $value);
        }
      }
    } else if($columns == '*'){
      $this->setColumn('*', '*');
    }
  }

  /**
   * Set table on which to perform the queries
   * @param string $table
   */
  public function setTable($table) {
    if (strlen($table) > 0) {
      $this->reset();
      $this->table = $table;
    }
  }

  /**
   * Set restrictions on current sql statement
   * @param string $column 
   * @param string $operator '=', '>', '<', '<>', '!=', 'LIKE', 'IN', 'FIND_IN_SET'
   * @param string $value
   * @param string $connector (optional) set AND|OR|XOR as connector between the last and this restriction
   * @throws Exception
   */
  public function setRestriction($column, $operator, $value, $connector = 'AND', $escapeValue = true) {
    if (!in_array($operator, $this->getAllowedOperators()))
      throw new Exception("Operator '" . $operator . "' is not allowed!");
    if ($column == '')
      throw new Exception("column value is empty!");
    if (!in_array($connector, $this->getAllowedConnectors()))
      throw new Exception("connector '" . $connector . "' is not allowed!");
    
    // IF SELECT IN () DO NOT ESCAPE VALUE
    if($operator == 'IN'){
      $escapeValue = false;
    }

    $this->restrictions[] = array('column' => $column, 'operator' => $operator, 'value' => $value, 'connector' => $connector, 'escapeValue' => $escapeValue);
  }

  /**
   * Set multiple restrictions
   * @param array $rows array('column', 'operator', 'value')
   */
  public function setRestrictions($rows) {
    if (is_array($rows) && count($rows) > 0)
      foreach ($rows as $int => $rowArray) {
        $row = $rowArray;
        if (!array_key_exists('column', $row))
          throw new Exception("column key not found!");
        if (!array_key_exists('operator', $row))
          throw new Exception("operator key not found!");
        if (!array_key_exists('value', $row))
          throw new Exception("value key not found!");
        if ($row['column'] == '')
          throw new Exception("column value is empty!");
        if ($row['operator'] == '')
          throw new Exception("operator value is empty!");
        if (!in_array($row['operator'], $this->getAllowedOperators()))
          throw new Exception("Operator '" . $operator . "' is not allowed!");

        if (array_key_exists('connector', $row) && !in_array($row['connector'], $this->getAllowedConnectors()))
          throw new Exception("connector '" . $connector . "' is not allowed!");
        
        if(!array_key_exists('escapeValue', $row))
                $row['escapeValue'] = true;

        $this->setRestriction($row['column'], $row['operator'], $row['value'], (isset($row['connector']) ? $row['connector'] : 'AND'), (isset($row['escapeValue']) ? (bool)$row['escapeValue'] : true));
      }
  }

  public function setOrder($field, $direction = 'ASC') {
    $allowedDirections = array('ASC', 'DESC');
    if (in_array(strtoupper($direction), $allowedDirections)) {
      $this->order[] = array($field, strtoupper($direction));
    }
  }

  private function getAllowedOperators() {
    return array('=', '>', '<', '<>', '!=', 'LIKE', 'IN', 'FIND_IN_SET');
  }

  private function getAllowedConnectors() {
    return array('AND', 'OR', 'XOR');
  }

  /**
   * Set custom sql query string
   * @param string $sql
   */
  public function setSQL($sql) {
    if (strlen($sql) > 0) {
      $this->isCustomQuery = true;

      $this->sqlString = $sql;
    }
  }
  
  /**
   * Set mysql_fetch_(object|array) method, default is object
   * @param string $method object|array
   */
  public function setFetchMethod($method = mysqlFetchMethod::OBJECT_METHOD) {
    if (in_array($method, array(mysqlFetchMethod::OBJECT_METHOD, mysqlFetchMethod::ARRAY_METHOD))) {
      $this->fetchMethod = $method;
    }
  }

  /**
   * Has the object columns assigned?
   * @return bool
   */
  public function hasColumns() {
    return count($this->columns) > 0 ? true : false;
  }

  /**
   * Has the object restrictions assigned?
   * @return bool
   */
  public function hasRestrictions() {
    return count($this->restrictions) > 0 ? true : false;
  }

  /**
   * Has the object order assigned?
   * @return bool
   */
  public function hasOrder() {
    return count($this->order) > 0 ? true : false;
  }

  /**
   * Checks if last query has affected rows
   * @return bool
   */
  public function hasAffectedRows() {
    return mysql_affected_rows($this->link) > 0 ? true : false;
  }

  /**
   * Send query to database
   * 
   * @param string $sql
   * @return ressource
   * @throws Exception
   */
  private function query($sql = '') {
    $ret = false;
    try {
      if (strlen($sql) > 0) {
        if ($this->connect()) {
          try {
            $ret = mysql_query($sql, $this->link);

            $this->last_insert_id = mysql_insert_id();
          } catch (Exception $e) {
            throw new Exception($this->sqlString . ', ' . $e->getFile() . ': ' . $e->getLine() . ': ' . mysql_error());
          }
        }
      }

      if (!$ret) {
        throw new Exception(_('mysql: Keine Tabelle gesetzt'));
      }
    } catch (Exception $e) {
      throw new Exception($this->sqlString . ', ' . $e->getFile() . ': ' . $e->getLine() . ': ' . mysql_error());
    }

    return $ret;
  }

  /**
   * Returns a single row
   * @return array|object
   */
  public function fetchRow() {
    $this->prepareSQL(mysqlQueryType::SELECT);

    $num_args = func_num_args();
    if ($num_args > 0) {
      $arg = func_get_arg(0);
      if ($arg == 'debug') {
        $this->debug();
      }
    }

    $method = null;
    $isObjectFetch = false;
    switch ($this->fetchMethod) {
      default:
      case mysqlFetchMethod::OBJECT_METHOD:
        $method = 'mysql_fetch_assoc';
        $isObjectFetch = true;
        break;
      case mysqlFetchMethod::ARRAY_METHOD:
        $method = 'mysql_fetch_array';
        break;
    }

    if ($method == null)
      throw new Exception(_('mysql: Keine fetch methode angegeben'));

    $ret = $this->query($this->sqlString);
    $this->result = $method($ret);
    
    if($isObjectFetch){
      return (object)$this->result;
    } else {
      return $this->result; 
    }
  }

  /*
   * Returns a list of rows 
   */

  public function fetchList() {
    $this->prepareSQL(mysqlQueryType::SELECT);

    $num_args = func_num_args();
    if ($num_args > 0) {
      $arg = func_get_arg(0);
      if ($arg == 'debug') {
        $this->debug();
      }
    }

    $ret = $this->query($this->sqlString);

    $method = null;
    switch ($this->fetchMethod) {
      default:
      case mysqlFetchMethod::OBJECT_METHOD:
        $method = 'mysql_fetch_assoc';
        break;
      case mysqlFetchMethod::ARRAY_METHOD:
        $method = 'mysql_fetch_array';
        break;
    }

    if ($method == null)
      throw new Exception(_('mysql: Keine fetch methode angegeben'));
    while ($row = $method($ret)) {
      $this->result[] = $row;
    }

    return $this->result;
  }

  /*
   * Insert a row
   */

  public function insertRow() {
    $this->prepareSQL(mysqlQueryType::INSERT);

    $num_args = func_num_args();
    if ($num_args > 0) {
      $arg = func_get_arg(0);
      if ($arg == 'debug') {
        $this->debug();
      }
    }

    $ret = $this->query($this->sqlString);
  }

  /*
   * Update a row
   */

  public function updateRow() {
    $this->prepareSQL(mysqlQueryType::UPDATE);

    $num_args = func_num_args();
    if ($num_args > 0) {
      $arg = func_get_arg(0);
      if ($arg == 'debug') {
        $this->debug();
      }
    }

    $ret = $this->query($this->sqlString);
  }

  /*
   * Delete a row
   */

  public function deleteRows() {
    $this->prepareSQL(mysqlQueryType::DELETE);

    $num_args = func_num_args();
    if ($num_args > 0) {
      $arg = func_get_arg(0);
      if ($arg == 'debug') {
        $this->debug();
      }
    }

    $ret = $this->query($this->sqlString);
  }
  
  /**
   * Perform custom sql query
   * You have to use setSQL($sql) first
   */
  public function queryCustomSQL(){
    if(strlen($this->sqlString)>0){
      $ret = $this->query($this->sqlString);
    }
  }

  /**
   * Has resultset
   * @return bool
   */
  public function hasRows() {
    return (is_array($this->result) && count($this->result) > 0 ? true : false);
  }
  
  /**
   * Get row count
   * @return int
   */
  public function rowCount() {
    return (is_array($this->result) ? count($this->result) : 0);
  }

  public function debug() {
    $debug = '<pre>';
    $debug .= '<p>SQL: ' . $this->sqlString . '</p>';
    $debug .= '<p>Columns: ' . print_r($this->columns, true) . '</p>';
    $debug .= '<p>Restrictions: ' . print_r($this->restrictions, true) . '</p>';
    $debug .= '</pre>';
    die($debug);
  }

  /*
   * returns the result
   */

  public function result() {
    return $this->result;
  }

  /*
   * reset sql string
   */

  private function clearSql() {
    $this->sqlString = '';
  }

}

?>
