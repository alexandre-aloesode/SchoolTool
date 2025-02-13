<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promotion extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Promotion_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }
    
    public function Promotion()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getPromotion',
			'POST' => 'postPromotion',
			'PUT' => 'putPromotion',
			'DELETE' => 'deletePromotion'
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
	
	private function getPromotion()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_Model->getPromotion($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function postPromotion()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_Model->postPromotion($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putPromotion()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_Model->putPromotion($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deletePromotion()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_Model->deletePromotion($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    public function PromotionEnd()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'PUT' => 'putPromotionToEnd',
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

    private function putPromotionToEnd()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Promotion_Model->putPromotionToEnd($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	public function PromotionFaithfulness() {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getPromotionFaithfulness',
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

	private function getPromotionFaithfulness()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true)
		{
			return ($this->Promotion_Model->getPromotionFaithfulness($params));
		}
		else
		{
			return  ($this->Status()->Denied());
		}
	}
}
