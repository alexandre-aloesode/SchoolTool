<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skill extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Skill_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function Skill()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getSkill',
			'POST' => 'postSkill',
			'PUT' => 'putSkill',
			'DELETE' => 'deleteSkill'
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
	
	private function getSkill()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Skill_Model->getSkill($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postSkill()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Skill_Model->postSkill($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putSkill()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Skill_Model->putSkill($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deleteSkill()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Skill_Model->deleteSkill($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}
