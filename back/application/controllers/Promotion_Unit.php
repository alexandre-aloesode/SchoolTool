<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promotion_Unit extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Promotion_Unit_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }

    public function PromotionUnit()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
            'GET' => 'getPromotionUnit',
            'POST' => 'postPromotionUnit',
			'DELETE' => 'deletePromotionUnit'
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

	private function getPromotionUnit()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_Unit_Model->GetPromotionUnit($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    private function postPromotionUnit()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_Unit_Model->postPromotionUnit($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deletePromotionUnit()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_Unit_Model->deletePromotionUnit($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}