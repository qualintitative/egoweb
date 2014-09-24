<?php
class ApiController extends Controller
{
	// Members
	/**
	 * Key which has to be in HTTP USERNAME and PASSWORD headers
	 */
	Const APPLICATION_ID = 'EGOWEB';

	/**
	 * Default response format
	 * either 'json' or 'xml'
	 */
	private $format = 'json';
	/**
	 * @return array action filters
	 */
	public function filters()
	{
			return array();
	}

	// Actions
	public function actionCreate()
	{
	    $headers = array();
	    foreach($_SERVER as $key => $value) {
	        if (substr($key, 0, 5) <> 'HTTP_') {
	            continue;
	        }
	        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
	        $headers[$header] = $value;
	    }

		if(isset($headers['api_key'])){
			// do something with api key
		}

		if(!isset($_POST['survey_id']) || !isset($_POST['user_id'])){
			$msg = "Missing survey_id and/or user_id parameter";
			$this->_sendResponse(419, $msg );
		}

		if($_POST['survey_id'] && $_POST['user_id']){
			$study = Study::model()->findByAttributes(array('name'=>$_POST['survey_id']));
			if(!$study){
				$msg = "Invalid survey_id";
				$this->_sendResponse(418, $msg );
			}
			$interview = Interview::getInterviewFromPrimekey($_POST['survey_id'], $_POST['user_id']);
			if($interview->completed == -1){
				$msg = "User already completed survey";
				$this->_sendResponse(420, $msg );
			}else{
				$data = array(
					'description'=>'User successfully logged into survey',
					'redirect_url'=>'',
				);
				$this->_sendResponse(200, CJSON::encode($data));
			}
		}
	}

	public function actionGet_survey()
	{
		if(isset($headers['api_key'])){
			// do something with api key
		}

		if(!isset($_GET['survey_id'])){
			$msg = "Missing survey_id parameter";
			$this->_sendResponse(419, $msg );
		}

		if($_GET['survey_id']){
			$study = Study::model()->findByAttributes(array('name'=>$_GET['survey_id']));
			if(!$study){
				$msg = $_GET['survey_id'] . " not found";
				$this->_sendResponse(404, $msg );
			}
			$interview = Interview::getInterviewFromPrimekey($_POST['survey_id'], $_POST['user_id']);
			$data = array(
				'description'=>'Survey successfully retrieved',
				'survey'=>array(
				),
			);
			$this->_sendResponse(200, CJSON::encode($data));
		}
	}

	public function actionGet_user()
	{
		if(isset($headers['api_key'])){
			// do something with api key
		}

		if(!isset($_GET['user_id'])){
			$msg = "Missing user_id parameter";
			$this->_sendResponse(419, $msg );
		}

		if($_GET['user_id']){
			$interviews = q("SELECT interviewId FROM answer WHERE value = ''")->queryColumn();
			if(!$participant){
				$msg = $_GET['user_id'] . " not found";
				$this->_sendResponse(404, $msg );
			}
			$interview = Interview::getInterviewFromPrimekey($_POST['survey_id'], $_POST['user_id']);
			$data = array(
				'description'=>'User successfully retrieved',
				'user'=>array(
					'id'=>$_GET['user_id'],
					'surveys_completed'=>$surveys_completed,
					'surveys_started'=>$surveys_started,
				),
			);
			$this->_sendResponse(200, CJSON::encode($data));
		}
	}

	private function _getStatusCodeMessage($status)
	{
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
			200 => 'OK',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
		);
		return (isset($codes[$status])) ? $codes[$status] : '';
	}

	private function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
	{
		// set the status
		$status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
		header($status_header);
		// and the content type
		header('Content-type: ' . $content_type);

		// pages with body are easy
		if($body != '')
		{
			// send the body
			echo $body;
		}
		// we need to create the body if none is passed
		else
		{
			// create some body messages
			$message = '';

			// this is purely optional, but makes the pages a little nicer to read
			// for your users.  Since you won't likely send a lot of different status codes,
			// this also shouldn't be too ponderous to maintain
			switch($status)
			{
				case 401:
					$message = 'You must be authorized to view this page.';
					break;
				case 404:
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
					break;
				case 500:
					$message = 'The server encountered an error processing your request.';
					break;
				case 501:
					$message = 'The requested method is not implemented.';
					break;
			}

			// servers don't always have a signature turned on
			// (this is an apache directive "ServerSignature On")
			$signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

			// this should be templated in a real-world solution
			$body = '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
	</head>
	<body>
		<h1>' . $this->_getStatusCodeMessage($status) . '</h1>
		<p>' . $message . '</p>
		<hr />
		<address>' . $signature . '</address>
	</body>
	</html>';

			echo $body;
		}
		Yii::app()->end();
	}
}