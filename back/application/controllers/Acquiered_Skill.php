<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Acquiered_Skill extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Acquiered_Skill_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
	}
	
	public function StudentSkill()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
            'GET' => 'getStudentSkill',
            'PUT' => 'putStudentSkill'
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

	private function getStudentSkill()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Acquiered_Skill_Model->getStudentSkill($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
    }
    
    private function putStudentSkill()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Acquiered_Skill_Model->putStudentSkill($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

    public function StudentSkillTotal()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudentSkillTotal'
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

	private function getStudentSkillTotal()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Acquiered_Skill_Model->getStudentSkillTotal($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

	public function StudentClassTotal()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudentClassTotal'
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

	private function getStudentClassTotal()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Acquiered_Skill_Model->getStudentClassTotal($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

	public function StudentPromotionClassTotal()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getStudentPromotionClassTotal'
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

	private function getStudentPromotionClassTotal()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Acquiered_Skill_Model->getStudentPromotionClassTotal($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}

	public function PromotionClassTotal()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getPromotionClassTotal'
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

	private function getPromotionClassTotal()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Acquiered_Skill_Model->getPromotionClassTotal($params));
        }
        else
        {
            return ($this->Status()->Denied());
        }
	}
}