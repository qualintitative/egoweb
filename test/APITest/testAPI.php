<?php

if ($argc < 3 )
{
    exit( "Usage: testAPI <egoweb_URL> <survey_link_password> <survey_link_id>\n" );
}

$FAILED_TESTS = 0;

$EGOWEB_URL = $argv[1];
$SURVEY_PASSWD = $argv[2];
$SURVEY_ID = $argv[3];
function callAPI($json){
    global $EGOWEB_URL;
	$url = $EGOWEB_URL.'/survey/getlink';
	$ch = curl_init();
	ob_start();  
    $out = fopen('php://output', 'w');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_STDERR, $out);  

	//curl_setopt($c, CURLOPT_HTTPPROXYTUNNEL, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	$ret = curl_exec($ch);
	fclose($out);  
	$debug = ob_get_clean();
	//echo $debug;
	$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($ret, 0, $header_len);
	$result = substr($ret, $header_len);

	curl_close($ch);
	if (empty($result)){
		throw new Exception("Empty response");
	}
	$response = json_decode( $result,true );
	if (empty($response)){
		throw new Exception("Invalid response".$result);
	}
	if(!empty($response['error'])){
		throw new Exception($response['error']);
	}
	return $response;
}

//positive test
try {
    global $SURVEY_PASSWD;
	print ("Positive test:");
	$json = json_encode(array(
		"password"=>"$SURVEY_PASSWD",
		"action"=> "passthrough",
		"user_id"=> "65:1",
		"survey_id"=> $SURVEY_ID,
		"redirect"=> "http://alp-respondent-portal:8888/index.php",
		"questions"=> null,
		"prefill"=> null

	));
	$response = callAPI($json);
	if (empty($response['link'])){
		print_r($response);
		throw new Exception("Missing link!");
	}
	if (empty($response['payload'])){
		throw new Exception("Missing payload!");
	}
	print ("Passed\n");

}catch (Exception $e){
    $FAILED_TESTS++;
	print "Failed: " . $e->getMessage()."\n";
}

//missing payload
try {
	print ("Missing post info test:");
	$response = callAPI('');
	print "Failed: Should have not got here\n";
}catch (Exception $e){
	$message = $e->getMessage();
	if ($message == 'Missing payload'){
		print("Passed\n");
	}else {
        $FAILED_TESTS++;
        print "Failed, got message: " . $e->getMessage() . "\n";
	}
}

//invalid payload
try {
	print ("non-json content test:");
	$json = "some none-json content";
	$response = callAPI($json);
	print "Failed: Should have not got here\n";
}catch (Exception $e){
	$message = $e->getMessage();
	if ($message == 'Unable to decode payload'){
		print("Passed\n");
	}else {
        $FAILED_TESTS++;
        print "Failed, got message: " . $e->getMessage() . "\n";
	}
}

//missing password
try {
	print ("missing password test:");
	$json = json_encode(array(
		"action"=> "passthrough",
		"user_id"=> "65:1",
		"survey_id"=> $SURVEY_ID,
		"redirect"=> "http://alp-respondent-portal:8888/index.php",
		"questions"=> null,
		"prefill"=> null

	));

	$response = callAPI($json);
	print "Failed: Should have not got here\n";
}catch (Exception $e){
	$message = $e->getMessage();
	if ($message == 'Please provide a valid password to access this feature.'){
		print("Passed\n");
	}else {
        $FAILED_TESTS++;
        print "Failed, got message: " . $e->getMessage() . "\n";
	}
}

//default password
try {
	print ("default password test:");
	$json = json_encode(array(
		"password"=>"yourpasswordhere",
		"action"=> "passthrough",
		"user_id"=> "65:1",
		"survey_id"=> $SURVEY_ID,
		"redirect"=> "http://alp-respondent-portal:8888/index.php",
		"questions"=> null,
		"prefill"=> null

	));

	$response = callAPI($json);
	print "Failed: Should have not got here\n";
}catch (Exception $e){
	$message = $e->getMessage();
	if ($message == 'Please provide a valid password to access this feature.'){
		print("Passed\n");
	}else {
        $FAILED_TESTS++;
        print "Failed, got message: " . $e->getMessage() . "\n";
	}
}

exit($FAILED_TESTS);
