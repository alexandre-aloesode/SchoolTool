<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Activity extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Activity_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Activity()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getActivity',
			'POST' => 'postActivity',
			'PUT' => 'putActivity',
			'DELETE' => 'deleteActivity'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getActivity()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Activity_Model->getActivity($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postActivity()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Activity_Model->postActivity($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putActivity()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Activity_Model->putActivity($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteActivity()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Activity_Model->deleteActivity($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

}
