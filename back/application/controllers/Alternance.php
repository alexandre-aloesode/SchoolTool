<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alternance extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Alternance_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Alternance()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getAlternance',
			'POST' => 'postAlternance',
			'PUT' => 'putAlternance',
			'DELETE' => 'deleteAlternance'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}

		echo json_encode($response);
	}

	private function getAlternance()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Alternance_Model->getAlternance($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postAlternance()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Alternance_Model->postAlternance($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putAlternance()
	{
		$params = $this->input->input_stream();
		
		if ($this->role_helper->Access($params) == true) {
			return ($this->Alternance_Model->putAlternance($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteAlternance()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Alternance_Model->deleteAlternance($params));
		} else {
			return ($this->Status()->Denied());
		}
	}
}
