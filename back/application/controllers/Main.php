<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends LPTF_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
	}
}
