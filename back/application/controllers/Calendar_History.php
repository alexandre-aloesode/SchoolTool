<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar_History extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Calendar_History_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
	}
	
	public function CalendarHistory()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
            'GET' => 'getCalendarHistory',
            'PUT' => 'putCalendarHistory',
            'POST' => 'postCalendarHistory',
            'DELETE' => 'deleteCalendarHistory'
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

	private function getCalendarHistory()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_History_Model->getCalendarHistory($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
    }

    private function postCalendarHistory()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_History_Model->postCalendarHistory($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
    
    private function putCalendarHistory()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_History_Model->putCalendarHistory($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

    private function deleteCalendarHistory()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_History_Model->deleteCalendarHistory($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

}