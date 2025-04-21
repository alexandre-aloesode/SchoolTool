<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Log_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Log()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getLog',
			'POST' => 'postLog',
			'PUT' => 'putLog',
			'DELETE' => 'deleteLog'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getLog()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Log_Model->getLog($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postLog()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Log_Model->postLog($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putLog()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Log_Model->putLog($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteLog()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Log_Model->deleteLog($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

}
