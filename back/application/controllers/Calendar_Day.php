<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar_Day extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Calendar_Day_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
	}
	
	public function CalendarDay()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
            'GET' => 'getCalendarDay',
            'PUT' => 'putCalendarDay',
            'POST' => 'postCalendarDay',
            'DELETE' => 'deleteCalendarDay'
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

	private function getCalendarDay()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_Day_Model->getCalendarDay($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
    }

    private function postCalendarDay()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_Day_Model->postCalendarDay($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
    
    private function putCalendarDay()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_Day_Model->putCalendarDay($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

    private function deleteCalendarDay()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_Day_Model->deleteCalendarDay($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    public function FullCalendarDays()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'POST' => 'postFullCalendarDays',
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

    private function postFullCalendarDays()
    {
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Calendar_Day_Model->postFullCalendarDays($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
    }

}