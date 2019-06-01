<?php
// your secret key
$secret = "6LfZDw0TAAAAAM73HeWJNSjC3DtuOwNcp_KLhRP7";
$emailTo = "christine.charpentier.avocate@gmail.com";

verify_and_send();

function verify_and_send(){
	$params = array(
		"email" => $_POST['email'], 
		"message" => $_POST['message'], 
		"to" => $GLOBALS['emailTo'],
		"subject" => "Contact From ".$_POST['name']);

	try{
		valid_captcha($_POST["g-recaptcha-response"]);
		valid_email($params);
		send_email($params);
		$data = array("type" => "success", "message" => "Email Successful");
	
	}catch(Exception $e){
		error_log("error ".$e->getMessage(), 0);
		header('HTTP/1.1 400 Bad Request');
		$data = array("type" => "error", "message" => $e->getMessage());
	}

	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($data);
}

function send_email($params){
	$headers = 'From: '. $GLOBALS['from'] . "\r\n" .
    'Reply-To: '. $params["email"] . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

	$status = mail($params["to"], $params["subject"], $params["message"], $headers);
	if($status != TRUE){
		throw new Exception("Cannot send the email");
	}
}

function valid_email($params){
	foreach ($params as $key => $value){
		if( empty($value) ){
			throw new InvalidArgumentException("Param " . $key . " is empty");
		}
	}
}

function valid_captcha($captcha) {
	$input = (is_null($captcha))? $captcha: trim($captcha);

	if(empty($input)){
		throw new InvalidArgumentException("Captcha response is empty");
	}
	// empty response
	$response = null;
	// check secret key
 	$response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$GLOBALS['secret']."&response=".$input."&remoteip=".$_SERVER['REMOTE_ADDR']);
	
	if($response == null || $response.success != true){
		throw new Exception("Captcha Failed");
	}
}
?>
