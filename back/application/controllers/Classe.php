<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Classe extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Class_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function Class()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getClass',
			'POST' => 'postClass',
			'PUT' => 'PutClass',
			'DELETE' => 'DeleteClass'
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
	
	private function getClass()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Class_Model->getClass($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postClass()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Class_Model->postClass($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putClass()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Class_Model->PutClass($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deleteClass()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Class_Model->deleteClass($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}
?>