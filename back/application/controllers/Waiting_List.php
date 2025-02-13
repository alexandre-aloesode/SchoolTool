<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Waiting_List extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Waiting_List_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function WaitingList()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getWaitingList',
			'POST' => 'postWaitingList',
			'PUT' => 'putWaitingList',
			'DELETE' => 'deleteWaitingList'
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
	
	private function getWaitingList()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Waiting_List_Model->getWaitingList($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postWaitingList()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Waiting_List_Model->postWaitingList($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putWaitingList()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Waiting_List_Model->putWaitingList($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deleteWaitingList()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Waiting_List_Model->deleteWaitingList($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }
    
    // JobWaitingList
    public function JobWaitingList()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'DELETE' => 'deleteJobWaitingList'
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
    
    private function deleteJobWaitingList()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Waiting_List_Model->deleteJobWaitingList($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }

    // UnitWaitingList
    public function UnitWaitingList()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'DELETE' => 'deleteUnitWaitingList'
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
    
    private function deleteUnitWaitingList()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Waiting_List_Model->deleteUnitWaitingList($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }
}
