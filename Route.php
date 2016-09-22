<?php

namespace Rooi;

class Route {

	/**
	* @var string (test patterns against it)
	*/
	private $uri;

	/**
	* @var string (GET or POST)
	*/
	private $request_method;

	/**
	* @var bool (set true if pattern was found and callback executed)
	*/
	public static $done;

	public function __construct() {
		self::$done = false;

		$uri = $_SERVER['REQUEST_URI'];

		// remove all accesive '/' -> www.google.com//////search
		$uri = preg_replace('#/+#', '/', $uri);
		$uri = rtrim($uri,"/");

		// relative path to our directory -> eg. uri = /rooi/profile, /rooi is the path, which needs to be cut
		$curr_dir = '/'.basename(__DIR__);
		$curr_dir = str_replace('/', '\/', $curr_dir);
		
		// remove relative part of path from URI
		if ( preg_match('/'.$curr_dir.'/', $uri) ){
			// $curr_dir has extra character = escaped forward slash
			$uri = substr($uri, (strlen($curr_dir) - 1) );
		}

		$this->uri = $uri;
		$this->request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
	}

	/**
	* Execute necesarry functions before matching the pattern, then call the callback
	* @param string -> pattern for specific route -> eg. /foo/:[0-9]+
	* @param callback -> function to call for matching route
	* @return void
	*/
	private function proceed_method($pattern, $callback){

		/*
		* For this uri were already completed defined actions 
		*/

		if ( self::$done )
			return;

		/*
		* Callback function is broken
		*/

		if ( !is_callable($callback) )
			return false;

		/*
		* Pattern matches any route, callback fired immediatelly
		*/

		if ( $pattern == '*' ){ 
			$callback();
		}
		
		// replace /foo with \/foo
		$copy = str_replace('/', '\/', $pattern); 

		if ( ( $this->request_method == 'GET' && empty($_POST) ) || ( $this->request_method == 'POST' && empty($_GET) ) ){

			// add marks: /^ = begin, $/ = end
			$str = '/^'.$copy.'$/'; 

			// params are in route pattern defined like this: /foo/:[0-9]+ -> foo/5 etc.
			if ( strpos($pattern, ':') ){

				// delete ':' from pattern 
				$tmp = str_replace(':', '', $pattern);

				$copy = str_replace('/', '\/', $tmp); 
				
				// replace variable $str with new value of a variable $copy
				$str = '/^'.$copy.'$/';

			}
				// test pattern against current uri
				if ( preg_match($str, $this->uri) ){

					// position of first param
					if ( $col = strpos($pattern, ':') ) { 
				
						// cut anything until first param
						$rest = substr($this->uri, $col);

						/*
						*	creating array of params -> possibilities:
						*		1. foo/:[]
						*		2. foo/:[]/:[]... 
						*		3. foo/:[A]/another/text/:[B]... = [A,B] not [A, another, text, B]
						*/

						// creates array from first param definition
						$params = explode('/', $rest);

						// create array from pattern definition
						$pattern_copy = ltrim(substr($pattern, $col));
						$pattern_arr = explode('/', $pattern_copy);
						$pattern_param_keys = [];
						
						// compares pattern definition array and param array 
						foreach ($pattern_arr as $pattern_item_key => $value) {
							if ($value[0] != ':'){
								// if there is param which is not defined as a param -> is deleted from param array
								unset($params[$pattern_item_key]);
							}
						}

						// fixes numeric indexing caused by deleting in param array
						$params = array_values($params);


						// matching route was completed
						self::$done = true; 
						$callback($params); 
					}
					else{

						self::$done = true;

						// no params for this route
						$callback(); 
					}
					
			}
				
		}
		else 
			return false;
	}

	/**
	* Route for GET request
	* @param string 
	* @param callback 
	* @return void
	*/
	public function get($pattern, $callback){

		$this->proceed_method($pattern, $callback);
	}

	/**
	* Route for POST request
	* @param string 
	* @param callback 
	* @return void
	*/
	public function post($pattern, $callback){

		$this->proceed_method($pattern, $callback);
	}

	/**
	* Get URI  
	* @return string
	*/
	public function get_uri(){

		return $this->uri;
	}

	/**
	* Set URI  
	* @param string
	* @return void
	*/
	public function set_uri($value){

		$this->uri = $value;
	}

	/**
	* Match part of uri using given param
	* @param string 
	* @return bool
	*/
	public function check_uri($param){

		return (substr($this->get_uri(), 0, strlen($param)) == $param);
	}

}

