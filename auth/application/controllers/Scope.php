<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/LPTF_Controller.php';

class Scope extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Scope_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Scope()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getScope',
			'POST' => 'postScope',
			'PUT' => 'putScope',
			'DELETE' => 'deleteScope'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getScope()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Scope_Model->getScope($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postScope()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Scope_Model->postScope($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putScope()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Scope_Model->putScope($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteScope()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Scope_Model->deleteScope($params));
		} else {
			return ($this->Status()->Denied());
		}
	}
}
