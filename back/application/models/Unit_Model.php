<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_Model extends LPTF_Model
{
    private $table = 'unit';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getUnit($params)
    {
        $constraints = [
            ['unit_id', 'optional', 'number', true], ['unit_name', 'optional', 'string'],
            ['unit_code', 'optional', 'string'], ['is_active', 'optional', 'boolean'],
            ['start_date', 'optional', 'none'], ['end_date', 'optional', 'none']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getUnitFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postUnit($params)
    {
        $constraints = [
            ['unit_name', 'mandatory', 'string'], ['unit_code', 'mandatory', 'string'],
            ['is_active', 'optional', 'boolean'], ['start_date', 'mandatory', 'string'],
            ['end_date', 'mandatory', 'string']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [
            'name' => $params['unit_name'],
            'code' => $params['unit_code'],
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date']
        ];

        if (array_key_exists('is_active', $params))
        {
            $data['is_active'] = $params['is_active'];
        }

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putUnit($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number'], ['unit_name', 'optional', 'string'],
            ['unit_code', 'optional', 'string'], ['is_active', 'optional', 'boolean'],
            ['start_date', 'optional', 'string'], ['end_date', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [];
        $optional_fields = [
            ['name', 'unit_name'],
            ['code', 'unit_code'],
            ['is_active', 'is_active'],
            ['start_date', 'start_date'],
            ['end_date', 'end_date']
        ];
    
        foreach ($optional_fields as $field)
        {
            if (array_key_exists($field[1], $params))
            {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        if (count($data) > 0)
        {
            $this->db->trans_start();
            $this->db->where('id', $params['unit_id']);
            $response = $this->db->update($this->table, $data);
            if (isset($params['is_active']) && $params['is_active'] === '0')
            {
                $sql = "DELETE unit_viewer FROM unit_viewer WHERE unit_fk = ?";
                $response = $this->db->query($sql, [$params['unit_id']]);

                $sql_update_job_visibility = "UPDATE job SET is_visible = 0 WHERE unit_fk = ?";
                $response_update_job_visibility = $this->db->query($sql_update_job_visibility, [$params['unit_id']]);
            }
            $this->db->trans_complete();
        }
        else
        {
            $response = $this->Status()->NoContent();
        }

        return $response;
    }

    public function deleteUnit($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->where_in('id', $params['unit_id']);
        $response = $this->db->delete($this->table);
        
        return ($response);
    }

    public function getUnitJob($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number', true], ['job_id', 'optional', 'number'],
            ['job_name', 'optional', 'string'], ['job_code', 'optional', 'string'],
            ['job_is_visible', 'optional', 'boolean']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->GetUnitJobFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postUnitJob($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number'], ['job_id', 'mandatory', 'number']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

		// Préparation du tableau pour récupérer les éléments du job
		$job = [
			"job_id" => $params["job_id"],
			"job_name" => "",
            "job_code" => "",
            "duration" => "",
            "min_students" => "",
            "max_students" => "",
            "link_subject" => "",
            "link_tutor_guide" => "",
            "description" => "",
            "is_visible" => "",
            "unit_id" => "",
		];

        // Récupération du Job
        $this->load->model('Job_Model');
		$getJob = $this->Job_Model->getJob($job);
        if (count($getJob) != 1)
        {
			return ($this->Status()->Forbidden());
        }

        $this->db->trans_start();
		// Reconstitution du tableau à insérer à partir du tableau récupéré
		$getJob = $getJob[0];
		$getJob["job_code"] = $getJob["job_code"]."_".$params["unit_id"];
		$getJob["job_name"] = $getJob["job_name"];
		$getJob["duration"] = $getJob["job_duration"];
		$getJob["min_students"] = $getJob["job_min_students"];
		$getJob["max_students"] = $getJob["job_max_students"];
		$getJob["link_subject"] = $getJob["job_link_subject"];
		$getJob["link_tutor_guide"] = $getJob["job_link_tutor_guide"];
		$getJob["description"] = $getJob["job_description"];
		$getJob["is_visible"] = $getJob["job_is_visible"];
		$getJob["unit_id"] = $params["unit_id"];

		// Insertion du job dans la unit
		$insertedJob = $this->Job_Model->postJob($getJob);
		if (!$this->Status()->IsValid())
			return ($this->Status()->Forbidden());

		//Récupération et insertion des compétences liées au projet
		$getJobSkills = [
			"needed" => "",
			"earned" => "",
			"skill_id" => "",
			"job_id" => $params["job_id"]
        ];
        $this->load->model('Job_Skill_Model');
		$jobSkills = $this->Job_Skill_Model->getJobSkill($getJobSkills);
		foreach ($jobSkills as $jobSkill)
		{
			$JobSkillsParams["needed"] = $jobSkill["skill_needed"];
			$JobSkillsParams["earned"] = $jobSkill["skill_earned"];
			$JobSkillsParams["skill_id"] = $jobSkill["skill_id"];
			$JobSkillsParams["job_id"] = strval($insertedJob);
			$insertedJobSkills = $this->Job_Skill_Model->postJobSkill($JobSkillsParams);
            if (!$insertedJobSkills)
            {
				return ($this->Status()->Forbidden());
			}
        }
        $this->db->trans_complete();

        return ($insertedJob);
    }

    public function postUnitDuplicate($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number'], ['unit_code', 'mandatory', 'string'],
            ['unit_name', 'mandatory', 'string'], ['start_date', 'mandatory', 'string'],
            ['end_date', 'mandatory', 'string'], ['is_active', 'optional', 'boolean']
        ];
        
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->trans_start();

        $inserted_unit = $this->postUnit($params);

        $data_existing_unit_job = [
            'unit_id' => $params['unit_id'],
            'job_id' => ''
        ];
        $duplicate_unit_job = $this->getUnitJob($data_existing_unit_job);

        foreach ($duplicate_unit_job as $job)
        {
            $data_new_unit_job = [
                'unit_id' => (string)$inserted_unit,
                'job_id' => $job['job_id']
            ];
            $this->postUnitJob($data_new_unit_job);
        }

        $data_existing_unit_goal = [
            'value' => '',
            'skill_id' => '',
            'unit_id' => $params['unit_id']
        ];

        $this->load->model('Unit_Goal_Model');
        $duplicate_unit_goal = $this->Unit_Goal_Model->getUnitGoal($data_existing_unit_goal);

        foreach ($duplicate_unit_goal as $goal) {
            $data_new_unit_goal = [
                'value' => $goal['unit_goal_value'],
                'skill_id' => $goal['skill_id'],
                'unit_id' => (string)$inserted_unit,
            ];

            $this->Unit_Goal_Model->postUnitGoal($data_new_unit_goal);
        }

        $this->db->trans_complete();

        return ($inserted_unit);
    }

    public function putUnitToEnd($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number']

        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->trans_start();

        $unit_jobs_params = [
            'unit_id' => $params['unit_id'],
            'job_id' => ''
        ];
        $unit_jobs_result = $this->getUnitJob($unit_jobs_params);

        foreach ($unit_jobs_result as $unit_job) {
            $sql_delete_regs = "DELETE FROM registration WHERE job_fk = ? AND is_complete = 0 AND click_date IS NULL";
            $response_delete_regs = $this->db->query($sql_delete_regs, [$unit_job['job_id']]);
        }

        $sql_unit_viewer = "DELETE uw FROM unit_viewer as uw WHERE unit_fk = ?";
        $response_unit_viewer = $this->db->query($sql_unit_viewer, [$params['unit_id']]);

        $unit_params = [
            'unit_id' => $params['unit_id'],
            'is_active' => '0',
        ];
        $response = $this->putUnit($unit_params);

        $this->db->trans_complete();

        return ($response);
    }

    private function getUnitFields()
    {
        return ([
			'unit_id' => [
                'type' => 'in', 
                'field' => 'id', 
                'filter' => 'where'
            ],
            'unit_name' => [
                'type' => 'in', 
                'field' =>'name', 
                'filter' => 'like'
            ],
            'unit_code' => [
                'type' => 'in',
                'field' =>'code', 
                'filter' => 'like'
            ],
            'is_active' => [
                'type' => 'in', 
                'field' =>'is_active', 
                'filter' => 'where'
            ],
            'start_date' => [
                'type' => 'in', 
                'field' => 'start_date', 
                'filter' => 'none'
            ],
            'end_date' => [
                'type' => 'in', 
                'field' => 'end_date', 
                'filter' => 'none'
            ]
        ]);
    }
        
    private function getUnitJobFields()
    {
        return([
            'unit_id' => [
                'type'=>'in',
                'field'=>'id',
                'filter'=>'where' 
            ],
            'job_id'=>[
                'type'=>'out',
                'link'=>[
                    ['left' => ['unit','id'],'right' => ['job','unit_fk']]
                ],
                'field'=>'id',
                'filter'=>'where'
            ],
            'job_name'=>[
                'type'=>'out',
                'link'=>[
                    ['left' => ['unit','id'],'right' => ['job','unit_fk']]
                ],
                'field'=>'name',
                'filter'=>'like'
            ],
            'job_code'=>[
                'type'=>'out',
                'link'=>[
                    ['left' => ['unit','id'],'right' => ['job','unit_fk']]
                ],
                'field'=>'code',
                'filter'=>'like'
            ],
            'job_is_visible'=>[
                'type'=>'out',
                'link'=>[
                    ['left' => ['unit','id'],'right' => ['job','unit_fk']]
                ],
                'field'=>'is_visible',
                'filter'=>'where'
            ]
        ]);    
    }
}

?>