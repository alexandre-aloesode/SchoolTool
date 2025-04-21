<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Followup extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Followup_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Followup()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getFollowup',
			'POST' => 'postFollowup',
			'PUT' => 'putFollowup',
			'DELETE' => 'deleteFollowup'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getFollowup()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Followup_Model->getFollowup($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postFollowup()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Followup_Model->postFollowup($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteFollowup()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Followup_Model->deleteFollowup($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putFollowup()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Followup_Model->putFollowup($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function StudentFollowup()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudentFollowup',
			'POST' => 'postStudentFollowup'
		];

		if (array_key_exists($method, $actions))
		{
			$call = $actions[$method];
			$response = $this->$call();
		}
		else
		{
			$response = $this->Status()->BadMethod();
		}
		echo json_encode($response);
	}

	private function getStudentFollowup()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Followup_Model->getStudentFollowup($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postStudentFollowup()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Followup_Model->postStudentFollowup($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}
