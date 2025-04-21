<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Student extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Student_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Student()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudent',
			'POST' => 'postStudent',
			'PUT' => 'putStudent',
			'DELETE' => 'deleteStudent'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getStudent()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Student_Model->getStudent($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postStudent()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Student_Model->postStudent($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putStudent()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Student_Model->putStudent($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteStudent()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Student_Model->deleteStudent($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function StudentActivity()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'DELETE' => 'deleteStudentActivity',
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function deleteStudentActivity()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Student_Model->deleteStudentActivity($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function StudentInactive()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudentInactive',
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getStudentInactive()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Student_Model->getStudentInactive($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function StudentFaithfulness()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudentFaithfulness',
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getStudentFaithfulness()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Student_Model->getStudentFaithfulness($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function StudentAttendance() {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudentAttendance',
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getStudentAttendance() {
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Student_Model->getStudentAttendance($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function GroupAttendance() {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getGroupAttendance',
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getGroupAttendance() {
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Student_Model->getGroupAttendance($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function NewBadges() {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'POST' => 'postNewBadges',
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function postNewBadges() {
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Student_Model->postNewBadges($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function LastLog() {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getLastLog',
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getLastLog() {
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Student_Model->getLastLog($params));
		} else {
			return ($this->Status()->Denied());
		}
	}
}
