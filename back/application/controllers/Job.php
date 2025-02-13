<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Job extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Job_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Job()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getJob',
			'POST' => 'postJob',
			'PUT' => 'putJob',
			'DELETE' => 'deleteJob'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getJob()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Job_Model->getJob($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postJob()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Job_Model->postJob($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putJob()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Job_Model->putJob($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteJob()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Job_Model->deleteJob($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function StudentJobAvailable()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudentJobAvailable'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private  function getStudentJobAvailable()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Job_Model->getStudentJobAvailable($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function JobStudentAvailable()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getJobStudentAvailable'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private  function getJobStudentAvailable()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Job_Model->getJobStudentAvailable($params));
		} else {
			return ($this->Status()->Denied());
		}
	}
}
