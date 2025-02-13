<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_Completed_Model extends LPTF_Model
{
    private $table = 'unit_completed';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getUnitCompleted($params)
    {
        $constraints = [
            ['unit_completed_id', 'optional', 'number'], ['completion_date', 'optional', 'none'],
            ['student_id', 'optional', 'number'], ['student_email', 'optional', 'string'],
            ['student_firstname', 'optional', 'string'], ['student_lastname', 'optional', 'string'],
            ['unit_id', 'optional', 'number', true], ['unit_code', 'optional', 'number'],
            ['unit_name', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getUnitCompletedFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postUnitCompleted($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['unit_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $unit_completed_data = [
            "student_id" => $params["student_id"],
            "unit_id" => $params["unit_id"]
        ];

        $is_unit_already_completed = $this->getUnitCompleted($unit_completed_data);
        if (count($is_unit_already_completed) > 0)
        {
            return;
        }

        date_default_timezone_set('Europe/Paris');

        $data = [
            'completion_date' => Date('Y-m-d H:i:s'),
            'student_fk' => $params['student_id'],
            'unit_fk' => $params['unit_id'],
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putUnitCompleted($params)
    {
        $constraints = [
            ['unit_completed_id', 'mandatory', 'number'], ['student_id', 'optional', 'number'],
            ['unit_id', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [];
        $optional_fields = [
            ['student_fk', 'student_id'],
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
            $this->db->where('id', $params['unit_completed_id']);
            $response = $this->db->update($this->table, $data);
        }
        else
        {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteUnitCompleted($params)
    {
        $constraints = [
            ['unit_completed_id', 'mandatory', 'number', true]
        ];
        
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->where_in('id', $params['unit_completed_id']);
        $response = $this->db->delete($this->table);
        
        return ($response);
    }

    public function postUnitRevalidate($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number']
        ];
        
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->load->model('Unit_Viewer_Model');
        $this->load->model('Unit_Goal_Model');
        $this->load->model('Acquiered_Skill_Model');

        $unit_students_params = [
            'unit_id' => $params['unit_id'],
            'student_id' => '',
            'student_firstname' => '',
            'student_lastname' => '',
        ];

        $unit_students = $this->Unit_Viewer_Model->getUnitStudent($unit_students_params);

        $unit_goals_params = [
            'unit_id' => $params['unit_id'],
            'value' => '',
            'skill_id' => '',
            'skill_name' => ''
        ];

        $unit_goals = $this->Unit_Goal_Model->getUnitGoal($unit_goals_params);
        if (count($unit_goals) === 0) return;

        foreach ($unit_students as $student)
        {
            $units_completed_params = [
                'unit_id' => $params['unit_id'],
                'student_id' => $student['student_id'],
                'unit_completed_id' => ''
            ];

            $units_completed = $this->getUnitCompleted($units_completed_params);
            if (count($units_completed) > 0)
            {
                $delete_unit_completed_params = [
                    'unit_completed_id' => $units_completed[0]['unit_completed_id']
                ];
                $this->deleteUnitCompleted($delete_unit_completed_params);
            }

            $is_unit_completed = true;

            foreach ($unit_goals as $goal)
            {
                $student_skill_params = [
                    'student_id' => $student['student_id'],
                    'skill_id' => $goal['skill_id']
                ];

                $student_skill = $this->Acquiered_Skill_Model->getStudentSkillTotal($student_skill_params);
                if (count($student_skill) === 0 || $student_skill[0]['earned'] < $goal['unit_goal_value'])
                {
                    $is_unit_completed = false;
                    break;
                }
            }
            if ($is_unit_completed)
            {
                $post_unit_completed_params = [
                    'student_id' => $student['student_id'],
                    'unit_id' => $params['unit_id']
                ];
                $this->postUnitCompleted($post_unit_completed_params);
            }
        }

        return true;
    }

    private function getUnitCompletedFields()
    {
        return ([
			'unit_completed_id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'completion_date' => [
                'type' => 'in',
                'field' => 'completion_date',
                'filter' => 'none'
            ],
            'student_id' => [
                'type' => 'in',
                'field' => 'student_fk',
                'alias' => 'student_id',
                'filter' => 'where'
            ],
            'unit_id' => [
                'type' => 'in',
                'field' => 'unit_fk',
                'alias' => 'unit_id',
                'filter' => 'where'
            ],
            'student_email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_completed', 'student_fk'], 'right' => ['student', 'id']],
                ],
                'field' => 'email',
                'alias' => 'student_email',
                'filter' => 'where'
            ],
            'student_firstname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_completed', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                ],
                'field' => 'firstname',
                'alias' => 'student_firstname',
                'filter' => 'where'
            ],
            'student_lastname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_completed', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                ],
                'field' => 'lastname',
                'alias' => 'student_lastname',
                'filter' => 'where'
            ],
            'unit_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_completed', 'unit_fk'], 'right' => ['unit', 'id']],
                ],
                'field' => 'name',
                'alias' => 'unit_name',
                'filter' => 'where'
            ],
            'unit_code' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_completed', 'unit_fk'], 'right' => ['unit', 'id']],
                ],
                'field' => 'code',
                'alias' => 'unit_code',
                'filter' => 'where'
            ],
        ]);
    }
}

?>