<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_Goal extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Unit_Goal_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function UnitGoal()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getUnitGoal',
			'POST' => 'postUnitGoal',
			'PUT' => 'putUnitGoal',
			'DELETE' => 'deleteUnitGoal'
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
	
	private function getUnitGoal()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Goal_Model->getUnitGoal($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postUnitGoal()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Goal_Model->postUnitGoal($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putUnitGoal()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Goal_Model->putUnitGoal($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deleteUnitGoal()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Goal_Model->deleteUnitGoal($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    public function UnitGoalStudent()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getUnitGoalStudent',
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

    private function getUnitGoalStudent()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Goal_Model->getUnitGoalStudent($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}
