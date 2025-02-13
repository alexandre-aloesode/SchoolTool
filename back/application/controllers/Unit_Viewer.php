<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_Viewer extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Unit_Viewer_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }

    public function UnitStudent()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
            'GET' => 'getUnitStudent',
            'POST' => 'postUnitStudent',
			'DELETE' => 'deleteUnitStudent'
		];

		if (array_key_exists($method,$actions))
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

	private function getUnitStudent()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Viewer_Model->GetUnitStudent($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    private function postUnitStudent()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Viewer_Model->postUnitStudent($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deleteUnitStudent()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Viewer_Model->deleteUnitStudent($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    public function UnitStudents()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
            'POST' => 'postUnitStudents',
            'DELETE' => 'deleteUnitStudents'
		];

		if (array_key_exists($method,$actions))
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
    
    private function postUnitStudents()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Viewer_Model->postUnitStudents($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }
    
    private function deleteUnitStudents()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Viewer_Model->deleteUnitStudents($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }
    
    public function UnitStudentsCurrent()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
            'POST' => 'postUnitStudentsCurrent',
		];

		if (array_key_exists($method,$actions))
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

    private function postUnitStudentsCurrent()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Viewer_Model->postUnitStudentsCurrent($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }

	public function StudentUnit()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudentUnit',
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

	private function getStudentUnit()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Viewer_Model->getStudentUnit($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    public function UnitsCompletedStudents()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
            'DELETE' => 'deleteUnitsCompletedStudents',
		];

		if (array_key_exists($method,$actions))
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

    private function deleteUnitsCompletedStudents()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Unit_Viewer_Model->deleteUnitsCompletedStudents($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}