<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alert extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Alert_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Alert()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getAlert',
			'POST' => 'postAlert',
			'PUT' => 'putAlert',
			'DELETE' => 'deleteAlert'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getAlert()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Alert_Model->getAlert($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postAlert()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Alert_Model->postAlert($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putAlert()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Alert_Model->putAlert($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteAlert()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Alert_Model->deleteAlert($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

}
