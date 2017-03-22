<?php


class Config {

	// max number of images which possible to upload into profile
	private static $max_profile_images = 5;

	private static $max_width = 800;
	private static $max_height = 600;

	// url of the project
	private static $HomeUrl="";

	//setting jsonrpc gate
	private static $projekt_api="";

	private static $projekt_api_url="";

	private static $projekt_api_url_sec="";

	private static $projekt_api_captcha_url="";
	
	private static $projekt_api_captcha_key="";

	private static $lang = '';

	private static $state = '';

	private static $langs = [
		'sk' => 'SVK',
		'cz' => 'CZE',
		'pl' => 'POL',
	];

	private static $currency_symbol = [
		'EUR' => '&euro;',
		'PLN' => '&#321;',
		'CZK' => 'Kč',
	];

	private static $loggedUser = '';
	private static $idprofile = '';
	private static $profileName = '';

	private static $states = [
		['short' => 'cz', 'shortcut' => 'CZE', 'state' => 'Česka republika', 'lang' => 'Česky jazyk', 'currency' => 'CZK'],
		['short' => 'sk','shortcut' => 'SVK', 'state' => 'Slovenská republika', 'lang' => 'Slovensky jazyk', 'currency' => 'EUR'],
		['short' => 'pl','shortcut' => 'POL', 'state' => 'Polska republika', 'lang' => 'Poľsky jazyk', 'currency' => 'PLN'],
	];

	/*
	* Return value declared in this file
	* @param property - name of the configuration value
	* @return if exists string value or null
	*/

	public static function get($property){

		if (isset($property) || property_exists('Config', $property)){
			return self::$$property; // creates variable from name PHPv5.3
		}
		else{
			return null;
		}
	}

	/*
	* Set value declared in this file
	* @param $property string name of the configuration
	* @param $value string - new value
	* @return boolean True if exists or False if not
	*/

	public static function set($property, $value){

        if (isset($property) || property_exists('Config', $property)){
			self::$$property = $value; // creates variable from name PHPv5.3
			return true;
		}
		else{
			return false;
		}
	}

	/*
	* It goes throung an array defined in this file
	* @param $nameArr - name of searched array
	* @param $eqVal - key that has to match
	* @param $retCol - return value of set column, if single arr -> empty
	* @return string
	*/

	public static function searchThroughArr($nameArr, $eqVal, $retCol = ''){

		if (property_exists('Config', $nameArr)){

			// simple array
			if ((count(self::$$nameArr) == count(self::$$nameArr, 1))){
				foreach (self::$$nameArr as $key => $value) {
					if ($key == $eqVal)
						return $value;
				}
			}
			// multidimensional array
			else {

				foreach (self::$$nameArr as $arr) {

					foreach ($arr as $key => $value) {
						if ($value == $eqVal)
							return $arr[$retCol]; 
					}
				}
			}
			
		}
	}



}
