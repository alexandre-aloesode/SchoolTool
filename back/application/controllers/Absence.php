<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Absence extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Absence_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Absence()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getAbsence',
			'POST' => 'postAbsence',
			'PUT' => 'putAbsence',
			'DELETE' => 'deleteAbsence'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}

		echo json_encode($response);
	}

	private function getAbsence()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Absence_Model->getAbsence($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postAbsence()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Absence_Model->postAbsence($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putAbsence()
	{
		$params = $this->input->input_stream();
		
		if ($this->role_helper->Access($params) == true) {
			return ($this->Absence_Model->putAbsence($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteAbsence()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Absence_Model->deleteAbsence($params));
		} else {
			return ($this->Status()->Denied());
		}
	}
}
