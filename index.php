<?php
	
	require 'Route.php';
	$route = new Rooi\Route();

	// example test for testing object inside route
	class HelloCar
	{

		public function get_hello($param){
			$this->view($param[0]);
		}

		private function view($param){
			echo $param;
		}
	};

	// similar to POST method (->post())

	$route->get('/profile', function ()
    {
        require 'profile.php';
        
    });

    $route->get('/user/:[0-9]+/text/:[a-z]+', function ($param)
    {
        var_dump($param);
        
    });

    $hello_obj = new HelloCar();

    $route->get('/car/:[a-z]+', function ($param) use ($hello_obj)
    {
        $hello_obj->get_hello($param);
        
    });

    require 'DB.php';
    require 'DB_Query.php';

    $db_query = new Rooi\DB_Query('127.0.0.1', 'test', 'user', '1234');


    /* EXAMPLE */

    /*
	* Create table 'test' with 2 columns: 'id' (INT, AUTO_INCREMENT, PRIMARY), 'name' (VARCHAR)
    */

    // id = 1
    $data = ['name' => 'Dan'];
    $db_query->insert('test', $data);
    // id = 2
    $data2 = ['name' => 'Alex'];
    $db_query->insert('test', $data2);

    $update_data = ['name' => 'Tim'];
    $db_query->update('test', $update_data, ['id', '=', 1 ]);


    $select_data = $db_query->getAll('test');
    var_dump($select_data['result']);

    $db_query->where(['id', '=', 2 ])->whereAnd(['name', '=', 'Alex' ]);
    $select_data = $db_query->get('test', ['name']);
    var_dump($select_data['result']);

    $db_query->delete('test', 'name', 'Tim');
    $db_query->delete('test', 'name', 'Alex');

    $select_data = $db_query->getAll('test');
    var_dump($select_data['result']);

