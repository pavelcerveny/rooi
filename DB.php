<?php

namespace Rooi\DB;

class DB {

	/**
	* @var PDO connection object
	*/
	protected $conn = null;

	/**
	* @var string (SQL query)
	*/
	protected $_query = null;

	/**
	* @var string (database error)
	*/
	protected $_error = false;
	
	/**
	* @var string (debug)
	*/
	protected $_error_msg = ""; 
	
	/**
	* @var number (number of returned rows)
	*/
	protected $count;

	/**
	* @var array
	*/
	protected $result;
	
	/**
	* @var array (parameters inside WHERE clause)
	*/
	protected $where_vars;

	/**
	* @var string (mysql etc.)
	*/
	protected $_db_type;

	/**
	* Creates connection to database
	* @param string -> database host
	* @param string -> database name
	* @param string -> username
	* @param string -> password
	* @param string -> database type [pgsql, mysql]
	* @return bool
	*/
	public function __construct($host, $name, $user, $pass, $dbtype = 'mysql'){

		$this->_error = false;
		$this->_error_msg = "";
		
		if (!empty($host) && !empty($name) && !empty($user) && !empty($pass) && !empty($dbtype)){

			$this->_db_type = $dbtype;

			$dsn = "{$dbtype}:host={$host};dbname={$name};user={$user};password={$pass}";

			try {
				$this->conn = new PDO($dsn);

				// debuging options -> ATTR_ERRMODE, ERRMODE_EXCEPTION
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

				// include czech symbols
				$this->conn->exec("SET client_encoding = 'utf8'");
			}

			catch (PDOException $e) {
				$this->_error = true;
				$this->_error_msg .= $e->getMessage();
			}
		}

		else {
			$this->_error = true;
			$this->_error_msg .= "Constructor: wrong conn parameters.<br>";
		}
		
		return $this;
	}

	/**
	* Destroy PDO session
	* @return void
	*/
	public function disconnect(){

		$this->conn = null;
	}

	/**
	* Return errors if any
	* @return array
	*/
	public function get_error(){

		$errs = array();

		if (!empty($this->_error_msg) )
			$errs['error_msg'] = $this->_error_msg;
		else
			$errs['error_msg'] = "";

		$errs['error'] = $this->_error;

		return $errs;
	}

	/**
	* Secure execution of given query and params
	* @param string -> a query to execute
	* @param array -> variables inside given query
	* @return bool
	*/
	protected function query($sql, $params = []){

		$this->_query = $this->conn->prepare($sql);

		if ( !empty($params) ){

			$this->binding_value($params);
		}

		if ($this->_query->execute()){
			return true;	
		}
        else {
            $this->_error = true;
            $this->_error_msg .= "The query could not been executed.<br>".$this->_query->errorCode()."<br>";

            return false;
        }

	}

	/**
	* Returns data from executed query
	* @param string -> a query to execute
	* @param array -> variables inside given query
	* @return array | null -> in case of error
	*/
	protected function fetchData($sql, $data){

		if ( $this->query($sql, $data) ) {


			if ( $this->_query->rowCount() == 1 ){
				$result = $this->_query->fetchAll(PDO::FETCH_ASSOC);
				return ['result' => $result[0]];
			}
			else if ( $this->_query->rowCount() > 1 ){
				return ['result' => $this->_query->fetchAll(PDO::FETCH_ASSOC), 'rowCount' => $this->_query->rowCount()];
			}
			else {
	            $this->_error = true;
	            $this->_error_msg .= "Error during fetching data from database.<br>";
	        }
		}

		return null;
	}

	/**
	* Secure binding params to given query
	* @param array 
	* @return void
	*/
	private function binding_value($params, $type = null){

		if ($params){
			$x = 1;
			foreach ($params as $param) {

				if ($type){
					if ($type == 'int')
						$this->_query->bindValue($x, $param, PDO::PARAM_INT);
				}
				else {
					$this->_query->bindValue($x, $param);	
				}
				
				$x++;
			}	
		}
	}

	

}