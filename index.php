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
