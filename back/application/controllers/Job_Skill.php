<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Job_Skill extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Job_Skill_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }

    public function JobSkill()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'GetJobSkill',
			'POST' => 'PostJobSkill',
			'PUT' => 'PutJobSkill',
			'DELETE' => 'DeleteJobSkill'
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

	private function getJobSkill()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Job_Skill_Model->GetJobSkill($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

	private function postJobSkill()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Job_Skill_Model->PostJobSkill($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

	private function putJobSkill()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Job_Skill_Model->PutJobSkill($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

	private function deleteJobSkill()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Job_Skill_Model->DeleteJobSkill($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
    }
}