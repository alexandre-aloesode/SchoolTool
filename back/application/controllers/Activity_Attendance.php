<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Activity_Attendance extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Activity_Attendance_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function ActivityAttendance()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getActivityAttendance',
			'POST' => 'postActivityAttendance',
			'PUT' => 'putActivityAttendance',
			'DELETE' => 'deleteActivityAttendance'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getActivityAttendance()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Activity_Attendance_Model->getActivityAttendance($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postActivityAttendance()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Activity_Attendance_Model->postActivityAttendance($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putActivityAttendance()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Activity_Attendance_Model->putActivityAttendance($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteActivityAttendance()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Activity_Attendance_Model->deleteActivityAttendance($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

}
