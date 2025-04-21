<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Calendar_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
	}
	
	public function Calendar()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
            'GET' => 'getCalendar',
            'PUT' => 'putCalendar',
            'POST' => 'postCalendar',
            'DELETE' => 'deleteCalendar'
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

	private function getCalendar()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_Model->getCalendar($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
    }

    private function postCalendar()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_Model->postCalendar($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
    
    private function putCalendar()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_Model->putCalendar($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

    private function deleteCalendar()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_Model->deleteCalendar($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}