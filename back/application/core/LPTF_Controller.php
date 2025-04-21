<?php

class LPTF_Controller extends CI_Controller
{
    static $Status_Helper;

    public function __construct()
    {
        parent::__construct();

        $this->handleHeader();
        // var_dump(TAB_ROLES);


        include APPPATH . 'third_party/JWT/JWT.php';
        $this->jwt = new JWT();

        $this->load->database();
        $this->load->model('Log_Model');
        $this->load->helper('Status_Helper');
        $this->load->helper('Token_Helper');

        LPTF_Controller::$Status_Helper = new Status_Helper();

        $this->token_helper = new Token_Helper($this);
        
        if (!$this->token_helper->verify_token()) {
            $this->Status()->ExpectationFailed();
            $this->logAction();
            die();
        } elseif (!$this->token_helper->token_valid()) {
            $this->Status()->TokenExpired();
            $this->logAction();
            die();
        } else {
            $this->load->helper('Role_Helper');
            $this->role_helper = new Role_Helper($this);
            $this->logAction($this->token_helper->controler->jwt->get_payload());
        }
    }

    protected function logAction($payload = false)
    {
        $role = $payload === false ? null : $payload["role"];

        if ($role !== null) {
            $role = $this->role_helper->getRoleIdFromName($role);
        }

        $email = $payload === false ? null : $payload["user_email"];
        $scope = $payload === false ? null : implode(",", $payload["scope"]);
        $url = $_SERVER['HTTP_HOST']; 

        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') { 
            $params = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            $url .= strstr($_SERVER['REQUEST_URI'], '?', true) ?: strstr($_SERVER['REQUEST_URI'], '/', true);
        } else if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {   
            $params = file_get_contents('php://input');
            $url .= strstr($_SERVER['REQUEST_URI'], '?', true) ?: $_SERVER['REQUEST_URI'];
        }
        
        $logData = [
            "ip" => $_SERVER['REMOTE_ADDR'],
            "email" => $email,
            "role" => $role,
            "url" => $url,
            "scope" => $scope,
            "params" => $params == null ? '' : $params,
            "method" => $_SERVER['REQUEST_METHOD'],
            "date" => date("Y-m-d H:i:s")
        ];

        $this->Log_Model->postLog($logData);
    }

    private function handleHeader()
    {
        header('Access-Control-Allow-Origin: *');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Content-Type: text/plain');
            header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
            header('Access-Control-Allow-Headers: token, Content-Type');
            header('Access-Control-Max-Age: 1728000');
            header('Content-Length: 0');
            die();
        }
    }

    public function Status()
    {
        return LPTF_Controller::$Status_Helper;
    }
}
