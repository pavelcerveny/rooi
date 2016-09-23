<?php

namespace Rooi;

class DB_Query extends DB {

	/**
	* @var array -> blocks of WHERE parts of query
	*/
	private $_where = [];
	
	/**
	* @var string -> LIMIT part of query
	*/
	private $_limit = '';
	
	/**
	* @var string -> whole SQL query
	*/
	private $_sql = '';
	
	/**
	* @var array -> values for variable params
	*/
	private $_bindValues = [];
	
	/**
	* @var string -> JOIN part of query
	*/
	private $_join = '';
	
	/**
	* @var string -> ORDER BY part of query
	*/
	private $_orderBy = '';

	public function __construct($dbhost, $dbname, $dbuser, $dbpass, $dbtype = 'mysql'){

		parent::__construct($dbhost, $dbname, $dbuser, $dbpass, $dbtype);

	}

	/**
	* Building query from defined parts
	* @return void
	*/
	private function buildQuery(){

		$this->builtJoin();

		$this->builtWhere();
		
		$this->builtLimit();
		
		$this->builtOrderBy();
	}

	/**
	* Set an initial value to all object variables 
	* @return void
	*/
	private function resetQuery(){

		$this->_where = [];

		$this->_sql = '';
		
		$this->_bindValues = [];
		
		$this->_join = '';
		
		$this->_orderBy = '';
	}

	/**
	* Select everything from given table
	* @param string 
	* @return array -> data
	*/
	public function getAll($table){

		$sql = "SELECT * FROM {$table}";

		$this->buildQuery();

		$sql .= $this->_sql;

		$data = $this->fetchData($sql, $this->_bindValues);
		
		$this->resetQuery();

		return $data;

	}

	/**
	* Select only passed colums of given table
	* @param string 
	* @param array
	* @return array -> data
	*/
	public function get($table, $selected_cols = []){

		$selected_cols_count = count($selected_cols);

		$x = 0;
		
		if ( $selected_cols_count > 0 ){

			if ( $selected_cols_count == 1 ){

				$sql = "SELECT {$selected_cols[0]} FROM {$table}";
			}
			else {
				$sql = "SELECT ";
				for ($i=0; $i < $selected_cols_count; $i++) { 
					
					$sql .= $selected_cols[$i];

					if ($x < ($selected_cols_count - 1) ){

						// SELECT foo, bar ...
						$sql .= ", ";
					}
					$x++;
				}

				$sql .= " FROM {$table} ";

			}
		}
		else {
			throw new Exception("Error: param selected_cols is empty", 1);
			
		}
		
		$this->buildQuery();

		$sql .= $this->_sql;

		$data = $this->fetchData($sql, $this->_bindValues);

		$this->resetQuery();

		return $data;
	}

	/**
	* Creates JOIN clause 
	* @param string
	* @param array -> [column, operator, value]
	* @param string -> eg. INNER, OUTER etc.
	* @return DB_Query object
	*/
	public function join($table, $cond = [], $type = ''){


		if ( $type ){
			$sql = " {$type} JOIN {$table} ON ";
		}
		else 
			$sql = " JOIN {$table} ON ";

		if ( count($cond) == 3 ){

			$operators = ['=', '>', '<', '<=', '>='];

			$col   	   = $cond[0];
			$operator  = $cond[1];
			$value 	   = $cond[2];

			// -> JOIN table ON foo = bar
			if ( in_array($operator, $operators) ){
				$sql .= "{$col} {$operator} {$value}";
			}

			$this->_join .= $sql;
		}
		return $this;

	}

	/**
	* Creates WHERE clause 
	* @param array -> [column, operator, value]
	* @param string -> AND, OR
	* @return DB_Query object
	*/
	public function where($where_cond = [], $bool_cond = ''){

		if ( count($where_cond) == 3 ){

			$operators = ['=', '>', '<', '<=', '>='];

			$col   	   = $where_cond[0];
			$operator  = $where_cond[1];
			$value 	   = $where_cond[2];


			if ( in_array($operator, $operators) ){

				if ( strlen($bool_cond) > 0 ){

					$sql = " {$bool_cond} {$col} {$operator} ? ";
				}
				else
					$sql = " WHERE {$col} {$operator} ? ";
				
				$where_arr = [

					'sql'   => $sql,
					'value' => $value,
				];

				$this->_where[] = $where_arr;

				return $this;
			}
		}
	}

	/**
	* Creates insert query and executes it 
	* @param string
	* @param array -> assoc array: [[col => value], ...]
	* @return bool
	*/
	public function insert($table, $data = []) {

		$columns = null;
		
		$data_values = [];
		
		$data_keys = [];
		
		$question_marks = "";
		
		$x = 0;
		
		$data_count = count($data);
		
		foreach($data as $key => $value)
		{
			
			$data_values[] = $value;

			// key needs to be string
			$data_keys[] = "$key";
		}
					
		$columns = implode(", ", $data_keys);

		// creates string contains ? for every value, multiple separated by comma
		while ($x < $data_count) {

			$question_marks .= "?";
		
			if ($x < ($data_count - 1))
				$question_marks .= ", ";
		
			$x++;
		}
		
		$sql = "INSERT INTO {$table} ({$columns})
				VALUES ({$question_marks})";

	    return $this->query($sql, $data_values) ?: false;

		
	}

	/**
	* Creates update query and executes it 
	* @param string
	* @param array -> assoc array: [[col => value], ...]
	* @param array
	* @return bool
	*/
	public function update($table, $data, $where = []){

		// builds WHERE clause
		$this->where($where);

		$set = "";

		$x = 0;
		
		$data_values = [];

		$data_count = count($data);

		// pairs of 'col = ?' after keyword SET separated by comma
		foreach ($data as $key => $value) {
			$set .= $key. " = ". "?";

			if ($x < ($data_count - 1) ){
				$set .= ", ";
			}
			$x++;
		}

		// values for parameters in query substitued using ?
		foreach ($data as $key => $value) {
			$data_values[] = $value;
		}

		// value for WHERE clause -> every UPDATE clause needs to have it
		$data_values[] = $this->_where[0]['value'];

		// $this->_where[0]['sql'] = WHERE clause string
		$sql = "UPDATE {$table} SET {$set}{$this->_where[0]['sql']}";

		$this->resetQuery();
		return $this->query($sql, $data_values) ?: false;	    	

	}

	/**
	* Creates delete query and executes it 
	* @param string
	* @param string
	* @param number
	* @return bool
	*/
	public function delete($table, $column, $id){

        $sql = "DELETE FROM {$table} WHERE {$column}=?";

        $data_values[] = $id;

        return $this->query($sql, $data_values, 'int') ?: false;

    }

    /**
	* Creates query clause WHERE from given parameters and concatenate with the rest query string
	* @return void
	*/
    private function builtWhere(){

		if ( !empty($this->_where)){

			/*
				$this->_where contains array of two valued array -> [0] => ['sql' => "foo == ?", 'values' => [0] => bar]
			*/
			if ( is_array($this->_where[0]) ){

				foreach ($this->_where as $keys ) {
					foreach ($keys as $key => $value) {
						
						if ( $key == 'sql' ){
							// concatenate to the whole sql string query
							$this->_sql .= $value;
						}
						if ( $key == 'value' ){
							// add values to the array of all values of parameters of the query
							array_push($this->_bindValues, $value);
						}
					
					}
				}
			}
			else {

				$this->_sql .= $this->_where['sql'];
				array_push($this->_bindValues, $this->_where['value']);
			} 
		}
	}

	/**
	* Creates query clause LIMIT from given parameters
	* @return void
	*/
	private function builtLimit(){

		if ( $this->_limit ){
			$sql = "LIMIT {$this->_limit} ";
			$this->_sql .= $sql;
		}
	}

	/**
	* Creates query clause ORDER BY from given parameters
	* @return void
	*/
	private function builtOrderBy(){

		if ( $this->_orderBy ){
			$sql = "ORDER BY {$this->_orderBy} ";
			$this->_sql .= $sql;
		}
	}

	/**
	* Creates query clause JOIN from given parameters
	* @return void
	*/
	private function builtJoin(){

		if ( $this->_join ){
			$this->_sql .= $this->_join;
		}
	}

	/**
	* Set limit variable
	* @param number
	* @return void
	*/
	public function limit($num) {

		$this->_limit = $num;
	}

	/**
	* Set order by variable
	* @param string
	* @return void
	*/
	public function orderBy($orderBy) {

		$this->_orderBy = $orderBy;
	}

	/**
	* Modification of method where, implicitly sets condition as AND
	* @param array
	* @return DB_Query object
	*/
	public function whereAnd($where_cond = array()){

		return $this->where($where_cond, 'AND');
	}

	/**
	* Modification of method where, implicitly sets condition as OR
	* @param array
	* @return DB_Query object
	*/
	public function whereOr($where_cond = array()){

		return $this->where($where_cond, 'OR');
	}

	/**
	* Debug method - allows access to a protected fetchData method in a DB class
	* @param string -> a query to execute
	* @param array -> variables inside given query
	* @return void
	*/
	public function raw_query($sql, $data){

		$this->fetchData($sql, $data);
	}

	/**
	* Select max value from given column in a table
	* @param string
	* @param string 
	* @return array -> data
	*/
	public function maxId($table, $column) {
		
		$sql = "SELECT MAX({$column}) FROM {$table}";
		
		$data = $this->fetchData($sql, $this->_bindValues);
		
		$this->resetQuery();

		return $data;
	}

	/**
	* Returns the ID of the last inserted row or a sequence value
	* @param string -> name of a sequence object from which will be return last value (value needs to be provided for PostgreSQL) 
	* @return string
	*/
    public function return_lastID($seq = null){
		
		return $this->conn->lastInsertId($seq);
		
	}

	/**
	* Starts a transaction
	* @return void
	*/
	public function begin_transaction(){
		
		return $this->conn->beginTransaction();
		
	}

	/**
	* Commits a transaction
	* @return void
	*/
	public function commit(){
		
		return $this->conn->commit();
		
	}

	/**
	* Rolls back a transaction
	* @return void
	*/
	public function roll_back(){
		
		return $this->conn->rollBack();
		
	}

}