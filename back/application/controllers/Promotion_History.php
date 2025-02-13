<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promotion_History extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Promotion_History_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function PromotionHistory()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getPromotionHistory',
			'POST' => 'postPromotionHistory',
			'PUT' => 'putPromotionHistory',
			'DELETE' => 'deletePromotionHistory'
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
	
	private function getPromotionHistory()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_History_Model->getPromotionHistory($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postPromotionHistory()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_History_Model->postPromotionHistory($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putPromotionHistory()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_History_Model->putPromotionHistory($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deletePromotionHistory()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_History_Model->deletePromotionHistory($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}
