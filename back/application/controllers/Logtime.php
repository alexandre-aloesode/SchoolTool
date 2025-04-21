<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logtime extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Logtime_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function Logtime()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getLogtime',
			'POST' => 'postLogtime',
			'PUT' => 'putLogtime',
			'DELETE' => 'deleteLogtime'
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
	
	private function getLogtime()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Logtime_Model->getLogtime($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	public function PromotionLogtime()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getPromotionLogtime',
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
	
	private function getPromotionLogtime()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Logtime_Model->getPromotionLogtime($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	public function StudentLastLogtime()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudentLastLogtime',
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
	
	private function getStudentLastLogtime()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true)
		{
			return ($this->Logtime_Model->getStudentLastLogtime($params));
		}
		else
		{
			return  ($this->Status()->Denied());
		}
	}

	public function PromotionLastLogtime()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getPromotionLastLogtime',
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
	
	private function getPromotionLastLogtime()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true)
		{
			return ($this->Logtime_Model->getPromotionLastLogtime($params));
		}
		else
		{
			return  ($this->Status()->Denied());
		}
	}

	public function PromotionLogtimeAverage()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getPromotionLogtimeAverage',
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
	
	private function getPromotionLogtimeAverage()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true)
		{
			return ($this->Logtime_Model->getPromotionLogtimeAverage($params));
		}
		else
		{
			return  ($this->Status()->Denied());
		}
	}

	public function StudentLastWeekHours() {
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true)
		{
			return ($this->Logtime_Model->getStudentLastWeekHours());
		}
		else
		{
			return  ($this->Status()->Denied());
		}
	}

	public function RealLogtime()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getRealLogtime',
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

	private function getRealLogtime()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Logtime_Model->getRealLogtime($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

}

