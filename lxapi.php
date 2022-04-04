<?php
/**
 * ### LetterXpress API ###
 *
 * Version 1.0 - 02/2019
 * Autor: Christian Stein
 *
 * Company: A&O Fischer GmbH & Co. KG
 * Address: Maybachstraße 9, 21423 Winsen (Luhe)
 * Web: https://www.aof.de
 *
 * Product: LetterXpress, 2014-2019
 * Support: support@letterxpress.de
 * Web: https://www.letterxpress.de
 * Doc: https://www.letterxpress.de/dokumentation/api
 * 
 */

class LxpApi {

	private $_base_url;
	private $_debug;			// (bool) true or false
	private $_debug_format='array'; // (string) array or json

	public function __construct($api_url, $is_live) {
		$this->_base_url = $api_url;
        $this->_debug = $is_live;
	}

	public function getBalance($data_array) {
		$jsonObjekt = $this->_getJsonfromArray($data_array);
		$response = $this->_curlRequest("getBalance", $jsonObjekt, "GET");
		return $this->_getArrayFromJson($response);
	}

	public function getPrice($data_array) {
		$jsonObjekt = $this->_getJsonfromArray($data_array);
		$response = $this->_curlRequest("getPrice", $jsonObjekt, "GET");
		return $this->_getArrayFromJson($response);
	}

	public function getJobs($data_array, $type='queue', $days=0) {
		if(!empty($type)) {
			$jsonObjekt = $this->_getJsonfromArray($data_array);
			$request_url = "getJobs/".$type;
			if(!empty($days)) $request_url.="/".$days;
			$response = $this->_curlRequest($request_url, $jsonObjekt, "GET");
			return $this->_getArrayFromJson($response);
		}
		return NULL;
	}

	public function getJobsDeleted($data_array, $type='deleted', $days=0) {
		if(!empty($type)) {
			$jsonObjekt = $this->_getJsonfromArray($data_array);
			$request_url = "getJobs/".$type;
			if(!empty($days)) $request_url.="/".$days;
			$response = $this->_curlRequest($request_url, $jsonObjekt, "GET");
			return $this->_getArrayFromJson($response);
		}
		return NULL;
	}

	public function getJob($data_array, $job_id=0) {
		if(!empty($job_id)) {
			$jsonObjekt = $this->_getJsonfromArray($data_array);
			$response = $this->_curlRequest("getJob/".$job_id, $jsonObjekt, "GET");
			return $this->_getArrayFromJson($response);
		}
		return NULL;
	}

	public function setJob($data_array) {
		$jsonObjekt = $this->_getJsonfromArray($data_array);
		$response = $this->_curlRequest("setJob", $jsonObjekt, "POST");
		return $this->_getArrayFromJson($response);
	}

	public function updateJob($data_array, $job_id=0) {
		if(!empty($job_id)) {
			$jsonObjekt = $this->_getJsonfromArray($data_array);
			$response = $this->_curlRequest("updateJob/".$job_id, $jsonObjekt, "PUT");
			return $this->_getArrayFromJson($response);
		}
		return NULL;
	}

	public function deleteJob($data_array, $job_id=0) {
		if(!empty($job_id)) {
			$jsonObjekt = $this->_getJsonfromArray($data_array);
			$response = $this->_curlRequest("deleteJob/".$job_id, $jsonObjekt, "DELETE");
			return $this->_getArrayFromJson($response);
		}
		return NULL;
	}

	public function listInvoices($data_array) {
		$jsonObjekt = $this->_getJsonfromArray($data_array);
		$response = $this->_curlRequest("listInvoices/", $jsonObjekt, "GET");
		return $this->_getArrayFromJson($response);
	}

	public function getInvoice($data_array, $invoice_id=0) {
		$jsonObjekt = $this->_getJsonfromArray($data_array);
		$response = $this->_curlRequest("getInvoice/".$invoice_id, $jsonObjekt, "GET");
		return $this->_getArrayFromJson($response);
	}

	public static function ConvertPdf($pfad) {
		return base64_encode(file_get_contents($pfad));
	}

	public static function GetMd5Checksum($string) {
		return md5($string);
	}

	private function _getJsonfromArray($array) {
		$json = json_encode($array, JSON_FORCE_OBJECT);
		if($this->_debug==true) {
			echo 'REQUEST:<br/><br/>';
			if($json!=NULL) {
				if($this->_debug_format=='json') echo $json.'<hr>';
				if($this->_debug_format=='array') {
					echo '<pre>';
					print_r($array);
					echo '</pre><hr>';
				}
			}
			else {
			   echo '### ARRAY NOT VALID FOR JSON ###<hr>';
			}
		}
		return $json;
	}

	private function _getArrayFromJson($json) {
		$data_array = json_decode($json);
		if($this->_debug==true) {
			echo 'RESPONSE:<br/><br/>';
			if($data_array!=NULL) {
				if($this->_debug_format=='json') echo $json.'<hr>';
				if($this->_debug_format=='array') {
					echo '<pre>';
					print_r($data_array);
					echo '</pre><hr>';
				}
			}
			else {
			   echo '### JSON NOT VALID ###<hr>';
			}
		}
		return $data_array;
	}

	private function _curlRequest($route, $json, $action) {
		$curl = curl_init();
		$opt_array[CURLOPT_MAXREDIRS]=10;
		$opt_array[CURLOPT_URL]=$this->_base_url.$route;
		$opt_array[CURLOPT_SSL_VERIFYPEER] = false;

		switch($action){
			case "POST":
				$opt_array[CURLOPT_POST] = true;
				$opt_array[CURLOPT_POSTFIELDS] = $json;
				break;
			case "GET":
				$opt_array[CURLOPT_CUSTOMREQUEST] = 'GET';
				$opt_array[CURLOPT_POSTFIELDS] = $json;
				break;
			case "PUT":
				$opt_array[CURLOPT_CUSTOMREQUEST] = 'PUT';
				$opt_array[CURLOPT_POSTFIELDS] = $json;
				break;
			case "DELETE":
				$opt_array[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				$opt_array[CURLOPT_POSTFIELDS] = $json;
				break;
			default:
				break;
		}

		$opt_array[CURLOPT_HTTPHEADER] = array('Content-type: application/json');
		$opt_array[CURLOPT_USERAGENT] = 'MozillaXYZ/1.0';
		$opt_array[CURLOPT_RETURNTRANSFER] = true;
		$opt_array[CURLOPT_CONNECTTIMEOUT] = 5;
		$opt_array[CURLOPT_TIMEOUT] = 30;
		curl_setopt_array($curl, $opt_array);

		$output = curl_exec($curl);
		if($output===false){
			die('CURL-Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
		}
		curl_close($curl);

		return $output;
	}

}