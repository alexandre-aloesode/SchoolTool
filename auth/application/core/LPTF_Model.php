<?php

class LPTF_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function Status()
    {
        return (LPTF_Controller::$Status_Helper);
    }
}

?>