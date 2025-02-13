<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_History extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Unit_History_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function UnitHistory()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getUnitHistory',
			'POST' => 'postUnitHistory',
			'PUT' => 'putUnitHistory',
			'DELETE' => 'deleteUnitHistory'
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
	
	private function getUnitHistory()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_History_Model->getUnitHistory($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postUnitHistory()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_History_Model->postUnitHistory($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putUnitHistory()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_History_Model->putUnitHistory($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deleteUnitHistory()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_History_Model->deleteUnitHistory($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}
