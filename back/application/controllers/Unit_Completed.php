<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_Completed extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Unit_Completed_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function UnitCompleted()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getUnitCompleted',
			'POST' => 'postUnitCompleted',
			'PUT' => 'putUnitCompleted',
			'DELETE' => 'deleteUnitCompleted'
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
	
	private function getUnitCompleted()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Completed_Model->getUnitCompleted($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postUnitCompleted()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Completed_Model->postUnitCompleted($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putUnitCompleted()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Completed_Model->putUnitCompleted($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deleteUnitCompleted()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Completed_Model->deleteUnitCompleted($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    public function UnitRevalidate()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'POST' => 'postUnitRevalidate',
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

    private function postUnitRevalidate()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Completed_Model->postUnitRevalidate($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}
