<?php

namespace Alexschwarz89\Browserstack\Screenshots;

class CheckSlack {

	public function checkSlack($token, $channel) {

		if(isset($token) && !empty($token) && $token = "" && $channel == "" ){

			return true;

		} else {

			return false;
		}

	}

	public function postToSlack($message,$attachments) {

		$data = "payload=" . json_encode(array(
		"channel"       =>  '',
		"text"          =>  $message,
		"attachments" => $attachments,
		"icon_emoji"    =>  ':browserstack:',
		"username"      => 'browserstack',
		));

		
		$ch = curl_init('');
		if($attachments != null) {
			curl_setopt($ch, CURLOPT_HTTPHEADERS, array('Content-Type: application/json'));
		}       
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}

}