<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/LPTF_Controller.php';

class Role extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Role_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Role()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getRole',
			'POST' => 'postRole',
			'PUT' => 'putRole',
			'DELETE' => 'deleteRole'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getRole()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Role_Model->getRole($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postRole()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Role_Model->postRole($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putRole()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Role_Model->putRole($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteRole()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Role_Model->deleteRole($params));
		} else {
			return ($this->Status()->Denied());
		}
	}
}
