<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/LPTF_Controller.php';

class User extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('New_User_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function User()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getUser',
			'POST' => 'postUser',
			'PUT' => 'putUser',
			'DELETE' => 'deleteUser'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getUser()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->New_User_Model->getUser($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postUser()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->New_User_Model->postUser($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putUser()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->New_User_Model->putUser($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteUser()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->New_User_Model->deleteUser($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function NewStudents()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'POST' => 'postNewStudents',
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function postNewStudents()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->New_User_Model->postNewStudents($params));
		} else {
			return ($this->Status()->Denied());
		}
	}
}
