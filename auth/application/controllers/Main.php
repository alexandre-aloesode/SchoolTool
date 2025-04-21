<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

	public function __construct() {
		parent::__construct();
	}

	public function index()
	{
		$this->load->view('index');
	}

	public function refresh()
	{
		header('Access-Control-Allow-Origin: *');
		
		// var_dump($this->input->post());
		$authtoken = $this->input->post("authtoken");

		if (!isset($authtoken) || $authtoken == "") {
			echo json_encode(["error" => "Invalid refreshtoken"]);
			return (false);
		}

		include APPPATH.'third_party/JWT/JWT.php';
		$this->load->model('User_model');

		// Récupération du secret depuis l'authtoken
		$this->jwt = new JWT();
		if (!$this->jwt->verify_token($authtoken)) {
			echo json_encode(["error" => "Invalid refreshtoken"]);
			return (false);
		}
		$payload = $this->jwt->get_payload();

		// Récupération de l'user lié à l'authtoken
		$authtoken = $this->User_model->GetUserToken($payload["secret"]);
		if (empty($authtoken)) {
			echo json_encode(["error" => "Invalid refreshtoken"]);
			return(false);
		}

		// Maintenant qu'on a authentifié l'authtoken, on génère le token
		$user = $this->User_model->GetUser(array("user.id" => $authtoken[0]["user_id"]));
		if (empty($user)) {
			echo json_encode(["error" => "You are not one of us.1"]);
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
			// 'exp'=> time() + (60*15)
			'exp' => time() + 15
		];

		$token = $jwt->generate($token_data);
		echo json_encode(["token" => $token]);
	}
}
