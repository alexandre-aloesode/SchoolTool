<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Applicant extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Applicant_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function Applicant()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getApplicant',
			'POST' => 'postApplicant',
			'PUT' => 'putApplicant',
			'DELETE' => 'deleteApplicant'
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
	
	private function getApplicant()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Applicant_Model->getApplicant($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postApplicant()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Applicant_Model->postApplicant($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putApplicant()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Applicant_Model->putApplicant($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deleteApplicant()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Applicant_Model->deleteApplicant($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }
    
    public function ApplicantStatus()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStatus'
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

    private function getStatus()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Applicant_Model->getStatus($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }
    
    public function Situations()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getSituations'
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

    private function getSituations()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Applicant_Model->GetSituations($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }
    
    public function Studies()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudies'
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

    private function getStudies()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Applicant_Model->getStudies($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	public function NewApplicant()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'POST' => 'postNewApplicant',
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

    public function NewApplicants()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'POST' => 'postNewApplicants',
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

	public function postNewApplicant()
	{
		$params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Applicant_Model->postNewApplicant($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postNewApplicants()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Applicant_Model->postNewApplicants($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}
?>