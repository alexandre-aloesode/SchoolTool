<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_Goal_Model extends LPTF_Model
{
    private $table = 'unit_goal';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getUnitGoal($params)
    {
        $constraints = [
            ['unit_goal_id', 'optional', 'number'], ['value', 'optional', 'number'],
            ['skill_id', 'optional', 'number'], ['skill_name', 'optional', 'string'],
            ['unit_id', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getUnitGoalFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postUnitGoal($params)
    {
        $constraints = [
            ['value', 'mandatory', 'number'], ['skill_id', 'mandatory', 'number'],
            ['unit_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [
            'value' => $params['value'],
            'skill_fk' => $params['skill_id'],
            'unit_fk' => $params['unit_id'],
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putUnitGoal($params)
    {
        $constraints = [
            ['unit_goal_id', 'mandatory', 'number'], ['value', 'optional', 'number'],
            ['skill_id', 'optional', 'number'], ['unit_id', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [];
        $optional_fields = [
            ['value', 'value'],
            ['skill_fk', 'skill_id'],
            ['unit_fk', 'unit_id']
        ];

        foreach ($optional_fields as $field) 
        {
            if (array_key_exists($field[1], $params)) 
            {
                if (strlen($params[$field[1]]) == 0) 
                {
                    $data[$field[0]] = null;
                } 
                else 
                {
                    $data[$field[0]] = $params[$field[1]];
                }
            }
        }

        if (count($data) > 0)
        {
            $this->db->where('id', $params['unit_goal_id']);
            $response = $this->db->update($this->table, $data);
        }
        else
        {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteUnitGoal($params)
    {
        $constraints = [
            ['unit_goal_id', 'mandatory', 'number', true]
        ];
        
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->where_in('id', $params['unit_goal_id']);
        $response = $this->db->delete($this->table);
        
        return ($response);
    }

    public function getUnitGoalStudent($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->load->model('Unit_Viewer_Model');
        $this->load->model('Acquiered_Skill_Model');
        $this->load->model('Unit_Completed_Model');

        $data_student_units = [
            "student_id" => $params['student_id'],
            "unit_id" => ""
        ];

        $student_units = $this->Unit_Viewer_Model->getStudentUnit($data_student_units);

        foreach ($student_units as $unit)
        {
            $unit_goal_data = [
                'unit_id' => $unit['unit_id'],
                'skill_id' => '',
                'value' => ''
            ];
    
            $unit_goal = $this->getUnitGoal($unit_goal_data);

            $isUnitCompleted = true;
            if (count($unit_goal) <= 0) $isUnitCompleted = false;
        
            foreach ($unit_goal as $goal) {
                $data_student_skill = [
                    'student_id' => $params['student_id'],
                    'skill_id' => $goal['skill_id'],
                    'skill_name' => ''
                ];
    
                $student_skill = $this->Acquiered_Skill_Model->getStudentSkillTotal($data_student_skill);
                if(count($student_skill) === 0)
                {
                    $isUnitCompleted = false;
                    break;
                }
                else
                {
                    if ($student_skill[0]['earned'] < $goal['unit_goal_value'])
                    {
                        $isUnitCompleted = false;
                        break;
                    }
                }
            }

            if ($isUnitCompleted) {
                $data_check_unit_goal = [
                    "student_id" => $params['student_id'],
                    "unit_id" => $unit['unit_id']
                ];
                $response = $this->Unit_Completed_Model->postUnitCompleted($data_check_unit_goal);
            }
        }

        if (isset($response))
        {
            return $response;
        }
        else
        {
            return false;
        }
    }

    private function getUnitGoalFields()
    {
        return ([
			'unit_goal_id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'value' => [
                'type' => 'in',
                'field' => 'value',
                'filter' => 'where'
            ],
            'skill_id' => [
                'type' => 'in',
                'field' => 'skill_fk',
                'alias' => 'skill_id',
                'filter' => 'where'
            ],
            'skill_name' => [
                'type' => 'out',
                'field' => 'name',
                'link'=> [
                    ['left'=>['unit_goal','skill_fk'], 'right' => ['skill','id']],
                ],
                'alias' => 'skill_name',
                'filter' => 'where'
            ],
            'unit_id' => [
                'type' => 'in',
                'field' => 'unit_fk',
                'alias' => 'unit_id',
                'filter' => 'where'
            ]
        ]);
    }
}

?>