<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'core/LPTF_Controller.php';
class Oauth extends LPTF_Controller {

	public function __construct() {
		parent::__construct();

		// include APPPATH.'third_party/JWT/JWT.php';
		$this->load->model('User_model');
		$this->load->helper('string');
	
	}

	public function index()
	{
		header('Access-Control-Allow-Origin: *');
		if (defined('IS_DEV')) {
			$google_client_id = "604347883543-cu73up3fqo5r9gn18tqpkf3tu9ud41s4.apps.googleusercontent.com";
		} else {
			$google_client_id = "776344449452-o05h7394ddmtomd0ga92v4p04va4bmb5.apps.googleusercontent.com";
		}

		$token_id = $this->input->post("token_id");

		$client = new Google_Client(['client_id' => $google_client_id]);
		try {
			$payload = $client->verifyIdToken($token_id);
		}
		catch (Exception $e) {
			echo json_encode(["error" => "You are not one of us."]);
			return(false);
		}
		if ($payload) {
			$google_id = $payload['sub'];
			$email = $payload['email'];
			$firstname = $payload['given_name'];
			$lastname = $payload['family_name'];
			// $locale = $payload['locale'];
			if (isset($payload['hd']))
				$domain = $payload['hd'];
			else
				$domain = "gmail.com";
		} else {
			echo json_encode(["error" => "You are not one of us."]);
			return(false);
		}

		$user = $this->User_model->GetUser(array('email' => $email));
		if (empty($user)) {
			echo json_encode(["error" => "You are not one of us."]);
			return(false);
		}

		// Récupération du scope
		$scope = $this->User_model->GetUserScope(array("scope.user_id" => $user[0]['id']));
		$scopes = array();
		foreach ($scope as $value) {
			array_push($scopes, $value["scope_value"]);
		}

		$jwt = new JWT();
		$token_data = [
            'user_id' => $user[0]['id'],
            'user_email' => $user[0]['email'],
			'role' => $user[0]['role'],
			'scope' => $scopes,
			'exp'=> time() + (60*15)
		];

		$api_token = $jwt->generate($token_data);

		// generation du authtoken
		$secret = random_string('alnum', 128);
		$this->User_model->GenerateSecret(array("user_id" => $user[0]["id"], "secret" => $secret));

		$authjwt = new JWT();
		$token_data = [
			'secret' => $secret
		];
		$authtoken = $authjwt->generate($token_data);

		echo json_encode(["authtoken" => $authtoken, "token" => $api_token]);
	}
}