<?php

namespace Connect;

use Connect\API as API;
use Connect\ClientInterface as ClientInterface;

class Booking extends API implements ClientInterface {

	private $db;

	public function __construct($dbConn) {
		$this->db = $dbConn;
	}

	public function handleMethod($method, $request) {
		$requestName = $method.'RQ';
		$responseName = $method.'RS';

		$decodedResponse = $this->$requestName($request);
		return $this->$responseName($decodedResponse);
	}

	public function getAvail($request) {
		return $this->handleMethod(__FUNCTION__, $request);
	}

	public function getAvailRQ($request, $method) {

		// $request
		// access db
		// format data
		$data = new \stdClass();
		return $this->execute($method, $data);

		
	}

	public function getAvailRS($response) {


	}


}
