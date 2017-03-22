<?php

abstract class API 
{
	use Guzzle;
	use Parser;

	static protected $instances = array();

    abstract protected function __construct();

    public static function getInstance() {
        $class = get_called_class();
        if (! array_key_exists($class, self::$instances)) {
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class];
    }

	public function send(){
		
		$this->guzzleSend();

	}

	public function decodeResponse($response)
	{
		$this->parse($response);
	}
}

interface IB
{
	public function GetAvail($data);


}

class B extends API implements IB
{

	public function GetAvailRQ($data){
		// creates an xml object from data and latte
		$this->send($object);
	}

	public function GetAvailRS($response)
	{

	}

	
}

interface IFacade 
{
	public function getAvailability();

}
