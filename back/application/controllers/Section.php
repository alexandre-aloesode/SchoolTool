<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Section extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Section_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function Section()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getSection',
			'POST' => 'postSection',
			'PUT' => 'putSection',
			'DELETE' => 'deleteSection'
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
	
	private function getSection()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Section_Model->getSection($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postSection()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Section_Model->postSection($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putSection()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Section_Model->putSection($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deleteSection()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Section_Model->deleteSection($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}
