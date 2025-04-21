<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/LPTF_Controller.php';

class Secret extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Secret_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Secret()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getSecret',
			'POST' => 'postSecret',
			'PUT' => 'putSecret',
			'DELETE' => 'deleteSecret'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getSecret()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Secret_Model->getSecret($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postSecret()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Secret_Model->postSecret($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putSecret()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Secret_Model->putSecret($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteSecret()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Secret_Model->deleteSecret($params));
		} else {
			return ($this->Status()->Denied());
		}
	}
}
