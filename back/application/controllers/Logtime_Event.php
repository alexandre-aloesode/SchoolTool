<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logtime_Event extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Logtime_Event_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Logtime_Event()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getLogtime_Event',
			'POST' => 'postLogtime_Event',
			'PUT' => 'putLogtime_Event',
			'DELETE' => 'deleteLogtime_Event'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getLogtime_Event()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Logtime_Event_Model->getLogtime_Event($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postLogtime_Event()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Logtime_Event_Model->postLogtime_Event($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putLogtime_Event()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Logtime_Event_Model->putLogtime_Event($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteLogtime_Event()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Logtime_Event_Model->deleteLogtime_Event($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

}
