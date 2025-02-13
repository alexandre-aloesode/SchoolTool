<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Job_Skill_Model extends LPTF_Model
{
    private $table = 'job_skill';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getJobSkill($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number'], ['job_name', 'optional', 'string'],
            ['skill_id', 'optional', 'number'], ['skill_name', 'optional', 'string'],
            ['needed', 'optional', 'number'], ['earned', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getJobSkillFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postJobSkill($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number'], ['skill_id', 'mandatory', 'number'],
            ['needed', 'mandatory', 'number'], ['earned', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        if ($params['earned'] < 0)
            $params['earned'] = 0; 

        if ($params['needed'] < 0)    
            $params['needed'] = 0;

        $data = [
            'needed' => $params['needed'],
            'earned' => $params['earned'],
            'job_fk' => $params['job_id'],
            'skill_fk' => $params['skill_id']
        ];

        $this->db->trans_start();
        $response = $this->db->insert($this->table, $data);
        $inserted_id = $this->db->insert_id();

        // Ajout du skill aux gens deja insrits au job
        $this->db->select('id as reg_id, member_fk as student_id');
        $this->db->where('registration.job_fk', $params['job_id']);
        $this->db->where('registration.is_done', 0);
        $query = $this->db->get('registration');
        $result = $query->result_array();

        foreach ($result as $reg)
        {
            $data = [
                'status' => 'En cours',
                'job_skill_fk' => $inserted_id,
                'registration_fk' => $reg['reg_id'],
                'student_fk' => $reg['student_id']
            ];

            $this->db->insert('acquiered_skill', $data);
        }
        $this->db->trans_complete();

        return ($inserted_id);
    }

    public function putJobSkill($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number'], ['skill_id', 'mandatory', 'number'],
            ['needed', 'optional', 'number'], ['earned', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [];

        $optional_fields = [
            ['needed', 'needed'],
            ['earned', 'earned']
        ];

        if ($params['earned'] < 0)
            $params['earned'] = 0; 

        if ($params['needed'] < 0)    
            $params['needed'] = 0;  
        
        foreach ($optional_fields as $field)
        {
            if (array_key_exists($field[1], $params))
            {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        if (count($data) > 0)
        {
            $this->db->where('skill_fk', $params['skill_id']);
            $this->db->where('job_fk', $params['job_id']);
            $response = $this->db->update($this->table, $data);
        }
        else
        {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteJobSkill($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number'], ['skill_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->where_in('skill_fk', $params['skill_id']);
        $this->db->where('job_fk', $params['job_id']);
        $response = $this->db->delete($this->table);
        
        return ($response);
    }

    private function getJobSkillFields()
    {
        return ([
            'job_id' => [
                'type' => 'in', 
                'field' =>'job_fk',
                'alias' => 'job_id',
                'filter' => 'where'
            ],
            'job_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['job_skill', 'job_fk'], 'right' => ['job', 'id']]
                ], 
                'field' =>'name', 
                'filter' => 'like',
            ],
            'skill_id' => [
                'type' => 'in', 
                'field' =>'skill_fk',
                'alias' => 'skill_id',
                'filter' => 'where'
            ],
            'skill_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['job_skill', 'skill_fk'], 'right' => ['skill', 'id']]
                ], 
                'field' =>'name', 
                'filter' => 'like',
            ],
            'needed' => [
                'type' => 'in', 
                'field' =>'needed',
                'alias' => 'skill_needed',
                'filter' => 'where'
            ],
            'earned' => [
                'type' => 'in', 
                'field' =>'earned',
                'alias' => 'skill_earned',
                'filter' => 'where'
            ],
        ]);
    }
}