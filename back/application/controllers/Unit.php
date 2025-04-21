<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit extends LPTF_Controller {

	public function __construct()
	{
		parent::__construct();

        $this->load->model('Unit_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function Unit()
    {
        $method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getUnit',
			'POST' => 'postUnit',
			'PUT' => 'putUnit',
			'DELETE' => 'deleteUnit'
		];

		if (array_key_exists($method, $actions))
		{
			$call = $actions[$method];
			$response = $this->$call();
		}
		else
		{
			$resonse = $this->Status()->BadMethod();
		}
		echo json_encode($response);
    }

    private function getUnit()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Model->getUnit($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

	private function postUnit()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Model->postUnit($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

	private function putUnit()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Model->putUnit($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

	private function deleteUnit()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Model->deleteUnit($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
    }
    
    public function UnitJob()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET'  => 'getUnitJob',
			'POST' => 'postUnitJob',
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

	public function getUnitJob()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Model->getUnitJob($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

    public function postUnitJob()
    {
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Model->postUnitJob($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

    public function UnitDuplicate()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'POST' => 'postUnitDuplicate',
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

    public function postUnitDuplicate()
    {
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Model->postUnitDuplicate($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

    public function UnitEnd()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'PUT' => 'putUnitToEnd',
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

    private function putUnitToEnd()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Model->putUnitToEnd($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}
