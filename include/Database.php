<?php

/**
 * Database Class
 */
class Database {

	/**
	 * @var   resource
	 */
	protected $res = NULL;

	/**
	 * @var   array
	 */
	public $errors = array();

	/**
	 * @var   resource
	 */
	public $last = NULL;

	/**
	 * @var   resource
	 */
	public $last_query = NULL;

	/**
	 * Constructor
	 *
	 * @param   string   $user       Username
	 * @param   string   $password   Password
	 * @param   string   $database   Database
	 * @param   string   $host       Host
	 */
	public function __construct($user, $password, $database, $host = 'localhost') {
		$this->res = mysql_connect($host, $user, $password);
		mysql_select_db($database, $this->res);
	}

	/**
	 * Execute a query
	 *
	 * @param   string   $q   Query
	 *
	 * @return  bool
	 */
	public function query($q) {

		$this->last = mysql_query($q, $this->res);
		$this->last_query = $q;

		if (!$this->last) {
			$this->errors[] = array('error' => mysql_error($this->res), 'sql' => $q);
			$fh = fopen(dirname(__FILE__) . '/../var/log/errorlog.txt', 'a');
			fwrite($fh, mysql_error($this->res) . "\n" . $q . "\n===========\n");
			fclose($fh);
		}

		return $this->last;
	}

	/**
	 * Clean field name
	 *
	 * @param   string   $name   Name
	 *
	 * @return  string
	 */
	public function cleanName($name) {
		$cleaned = preg_replace('#[^a-zA-Z0-9_]#', '', $name);
		return $cleaned;
	}

	/**
	 * Escape field value
	 *
	 * @param   string   $value   Value
	 *
	 * @return  string
	 */
	public function escape($value) {
		$escaped = mysql_real_escape_string($value, $this->res);
		return $escaped;
	}

	/**
	 * Make mysql formatted date
	 *
	 * @param   mixed   $date   Int or string
	 * @param   mixed   $type   date/time/datetime
	 *
	 * @return  string
	 */
	public function date($date = 'now', $type = 'datetime') {

		$time = is_int($date) ? $date : strtotime($date);

		switch ($type) {
			case 'date':
				$format = 'Y-m-d';
				break;
			case 'time':
				$format = 'H:i:s';
				break;
			case 'datetime':
			default:
				$format = 'Y-m-d H:i:s';
				break;
		}

		$mysql = date($format, $time);

		return $mysql;
	}

	/**
	 * Empty a table
	 *
	 * @param   string   $table   Table name
	 *
	 * @return  bool
	 */
	public function emptyTable($table) {

		$table = $this->cleanName($table);

		//Truncate won't work on a locked table
		//$result = $this->query("TRUNCATE TABLE $table;");
		$result = $this->query("DELETE FROM $table;");
		if ($result) {
			$this->query("ALTER TABLE AUTO_INCREMENT =1");
		}

		return $result;
	}

	/**
	 * Lock/Unlock a table
	 *
	 * @param   string   $table   Table name
	 * @param   bool     $lock    Lock = true, Unlock = false
	 *
	 * @return  bool
	 */
	public function lock($table, $lock = true) {

		$table = $this->cleanName($table);

		if ($lock) {
			$result = $this->query("LOCK TABLES $table WRITE;");
		} else {
			$result = $this->query("UNLOCK TABLES");
		}

		return $result;
	}

	/**
	 * Get fields
	 *
	 * @param   string   $table   Table name
	 * @param   bool     $blank   Return as blank key => value object instead
	 *
	 * @return  array()
	 */
	public function getFields($table, $blank = false) {

		$table = $this->cleanName($table);

		if (!isset($this->fields[$table])) {

			$result = $this->query("DESCRIBE $table;");
			if (!$result) {
				return $result;
			}

			$rows = array();
			while ($row = $this->getRow()) {
				$rows[] = $row->Field;
			}

			$this->fields[$table] = $rows;
		}

		$keys = $this->fields[$table];

		if ($blank) {
			$blank = (object)array_combine($keys, array_fill(0, count($keys), ''));
			return $blank;
		}

		return $keys;
	}

	/**
	 * Get a row from the result set
	 *
	 * @param   resource   $result   Mysql result or NULL for last result
	 * @param   bool       $object   Object or array
	 *
	 * @return  mixed
	 */
	public function getRow($result = NULL, $object = true) {

		if ($result === NULL) {
			$result = $this->last;
		}

		if ($object) {
			$row = mysql_fetch_object($result);
		} else {
			$row = mysql_fetch_assoc($result);
		}

		return $row;
	}

	/**
	 * Get a value from the result set
	 *
	 * @param   resource   $result   Mysql result or NULL for last result
	 * @param   int        $row      Row number
	 *
	 * @return  mixed
	 */
	public function getValue($result = NULL, $row = 0) {

		if ($result === NULL) {
			$result = $this->last;
		}

		$value = mysql_result($result, $row);

		return $value;
	}

	/**
	 * Return number of rows from last operation
	 *
	 * @param   resource   $result   Mysql result or NULL for last result
	 *
	 * @return  int
	 */
	public function getNumRows($result = NULL) {

		if ($result === NULL) {
			$result = $this->last;
		}

		$num = mysql_num_rows($result);

		return $num;
	}

	/**
	 * Insert a row of data
	 *
	 * @param   string   $table   Table name
	 * @param   array    $data     Data
	 *
	 * @return  bool
	 */
	public function insertRow($table, $data) {

		$table = $this->cleanName($table);

		$fieldnames = $this->getFields($table);
		$values = array();

		foreach ($fieldnames as $field) {
			if (isset($data[$field])) {
				$fields[] = "`" . $field . "`";
				$values[] = "'" . $this->escape($data[$field], $this->res) . "'";
			}
		}

		$query = "INSERT INTO $table (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")";

		$result = $this->query($query);

		return $result;
	}

	/**
	 * Update data
	 *
	 * @param   string   $table   Table name
	 * @param   array    $data    Data
	 * @param   where    $data    Where clause
	 * @param   limit    $limit   Limit number of writes
	 *
	 * @return  bool
	 */
	public function update($table, $data, $where, $limit = 1) {

		$table = $this->cleanName($table);

		$fieldnames = $this->getFields($table);
		$set = array();

		foreach ($fieldnames as $field) {
			if (isset($data[$field])) {
				$set[] = "`" . $field . "` = '" . $this->escape($data[$field], $this->res) . "'";
			}
		}

		$query = "UPDATE $table SET " . implode(", ", $set) . " WHERE (" . $where . ")";

		if ($limit) {
			$query .= " LIMIT " . (int)$limit;
		}

		$result = $this->query($query);

		return $result;
	}


	/**
	 * Delete data
	 *
	 * @param   string   $table   Table name
	 * @param   where    $data    Where clause
	 * @param   limit    $limit   Limit number of writes
	 *
	 * @return  bool
	 */
	public function delete($table, $where, $limit = 1) {

		$table = $this->cleanName($table);

		$query = "DELETE FROM $table WHERE (" . $where . ")";

		if ($limit) {
			$query .= " LIMIT " . (int)$limit;
		}

		$result = $this->query($query);

		return $result;
	}

	/**
	 * Get insert ID of last insert
	 *
	 * @return  int
	 */
	public function getInsertId() {

		$id = mysql_insert_id($this->res);

		return $id;
	}

	/**
	 * Splits a string of multiple queries into an array of individual queries.
	 *
	 * @param   string  $sql  Input SQL string with which to split into individual queries.
	 *
	 * @return  array  The queries from the input string separated into an array.
	 *
	 * @source  Joomla Platform
	 */
	public static function splitSql($sql) {
		$start = 0;
		$open = false;
		$char = '';
		$end = strlen($sql);
		$queries = array();

		for ($i = 0; $i < $end; $i++)
		{
			$current = substr($sql, $i, 1);
			if (($current == '"' || $current == '\''))
			{
				$n = 2;

				while (substr($sql, $i - $n + 1, 1) == '\\' && $n < $i)
				{
					$n++;
				}

				if ($n % 2 == 0)
				{
					if ($open)
					{
						if ($current == $char)
						{
							$open = false;
							$char = '';
						}
					}
					else
					{
						$open = true;
						$char = $current;
					}
				}
			}

			if (($current == ';' && !$open) || $i == $end - 1)
			{
				$queries[] = substr($sql, $start, ($i - $start + 1));
				$start = $i + 1;
			}
		}

		return $queries;
	}

}

