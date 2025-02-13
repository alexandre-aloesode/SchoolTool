<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Invoice extends LPTF_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Invoice_Model');
	}

	public function index()
	{
		echo json_encode(["whoami" => "api"]);
	}

	public function Invoice()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getInvoice',
			'POST' => 'postInvoice',
			'PUT' => 'putInvoice',
			'DELETE' => 'deleteInvoice'
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}

		echo json_encode($response);
	}

	private function getInvoice()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Invoice_Model->getInvoice($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function postInvoice()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Invoice_Model->postInvoice($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function putInvoice()
	{
		$params = $this->input->input_stream();
		
		if ($this->role_helper->Access($params) == true) {
			return ($this->Invoice_Model->putInvoice($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	private function deleteInvoice()
	{
		$params = $this->input->input_stream();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Invoice_Model->deleteInvoice($params));
		} else {
			return ($this->Status()->Denied());
		}
	}

	public function InvoiceCount()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getInvoiceCount',
		];

		if (array_key_exists($method, $actions)) {
			$call = $actions[$method];
			$response = $this->$call();
		} else {
			$response = $this->Status()->BadMethod();
		}

		echo json_encode($response);
	}

	private function getInvoiceCount()
	{
		$params = $this->input->get();
		if ($this->role_helper->Access($params) == true) {
			return ($this->Invoice_Model->getInvoiceCount($params));
		} else {
			return ($this->Status()->Denied());
		}
	}
}
