<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Registration extends LPTF_Controller {

	public function __construct() 
	{
		parent::__construct();

        $this->load->model('Registration_Model');
	}

	public function index()
	{
		echo json_encode(["whoami"=>"api"]);
    }

    // Registration 
    public function Registration()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getRegistration',
            'PUT' => 'putRegistration',
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

	private function getRegistration()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->getRegistration($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }

    private function putRegistration(){
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->putRegistration($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }
    
    // JobStudent
    public function JobStudent()
    {
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getJobStudent',
			'DELETE'=>'deleteJobStudent',
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
 
	private function getJobStudent()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->getJobStudent($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }
    
	private function deleteJobStudent()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->deleteJobStudent($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
    
    // Job Status
    public function getJobAwait()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
        {
            echo json_encode($this->Status()->BadMethod());
            return ;
        }

        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            $params['click_date'] = 'null';
            $params['group_is_valid'] = '0';
            $response = $this->Registration_Model->getRegistration($params);
            echo json_encode($response);
        }
        else
        {
            echo json_encode($this->Status()->Denied());
        }
    }

    public function getJobProgress()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
        {
            echo json_encode($this->Status()->BadMethod());
            return ;
        }

        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            $params['click_date'] = 'null';
            $params['group_is_valid'] = '1';
            $response = $this->Registration_Model->getRegistration($params);
            echo json_encode($response);
        }
        else
        {
            echo json_encode($this->Status()->Denied());
        }
    }

    public function getJobReady()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
        {
            echo json_encode($this->Status()->BadMethod());
            return ;
        }

        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            $params['click_date'] = 'notnull';
            $params['group_is_valid'] = '1';
            $params['job_is_complete'] = '0';
            $response = $this->Registration_Model->getRegistration($params);
            echo json_encode($response);
        }
        else
        {
            echo json_encode($this->Status()->Denied());
        }
    }

    public function getJobChecked()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
        {
            echo json_encode($this->Status()->BadMethod());
            return ;
        }

        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            $params['job_is_done'] = '1';
            $response = $this->Registration_Model->getRegistration($params);
            echo json_encode($response);
        }
        else
        {
            echo json_encode($this->Status()->Denied());
        }
    }

    public function getJobDone()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET')
        {
            echo json_encode($this->Status()->BadMethod());
            return ;
        }

        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            $params['job_is_complete'] = '1';
            $response = $this->Registration_Model->getRegistration($params);
            echo json_encode($response);
        }
        else
        {
            echo json_encode($this->Status()->Denied());
        }
    }

    // Group
    public function Group()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getGroup',
			'POST' => 'postGroup',
			'PUT' => 'putGroup',
			'DELETE' => 'deleteGroup'
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

	private function getGroup()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->getGroup($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }

    private function postGroup()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->postGroup($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putGroup()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->putGroup($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function deleteGroup()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->deleteGroup($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }

    // GroupValidity
    public function GroupValidity()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'PUT' => 'putGroupValidity',
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
    
    private function putGroupValidity()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->putGroupValidity($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    // GroupReview
    public function GroupReview()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
            'GET' => 'getGroupReview',
            'PUT' => 'putGroupReview'
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

	private function getGroupReview()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->getGroupReview($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	private function putGroupReview()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->putGroupReview($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }

    // Member
	public function Member()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'POST'=>'postMember',
			'DELETE'=>'deleteMember'
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

	private function postMember()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->postMember($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }
    
	private function deleteMember()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->deleteMember($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
    }
    
    // GroupAvailabale
    public function GroupAvailable()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getGroupAvailable'
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

	private function getGroupAvailable()
	{
        $params = $this->input->get();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->getGroupAvailable($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    // GroupClick
	public function GroupClick()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'PUT' => 'putGroupClick'
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

	private function putGroupClick()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->putGroupClick($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    // Job Evaluator
    public function JobCorrector()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'GET' => 'getJobCorrector'
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

	private function getJobCorrector()
	{
        $params = $this->input->input_stream();
        if ($this->scope_helper->Access($params) == true)
        {
            return ($this->Registration_Model->getJobCorrector($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

	public function JobReview()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'PUT' => 'putJobReview'
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

	private function putJobReview()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->putJobReview($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}

    public function UnitReview()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$actions = [
			'PUT' => 'putUnitReview'
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

	private function putUnitReview()
	{
        $params = $this->input->input_stream();
        if ($this->role_helper->Access($params) == true)
        {
            return ($this->Registration_Model->putUnitReview($params));
        }
        else
        {
            return  ($this->Status()->Denied());
        }
	}
}