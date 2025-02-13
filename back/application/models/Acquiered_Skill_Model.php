<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Acquiered_Skill_Model extends LPTF_Model
{
    private $table = 'acquiered_skill';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getStudentSkill($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['skill_id', 'optional', 'number'],
            ['skill_name', 'optional', 'string'], ['class_id', 'optional', 'number'],
            ['class_name', 'optional', 'string'], ['job_id', 'optional', 'number'],
            ['job_name', 'optional', 'string'], ['group_id', 'optional', 'number'],
            ['group_name', 'optional', 'string'], ['unit_id', 'optional', 'number'],
            ['unit_name', 'optional', 'string'], ['status', 'optional', 'string'],
            ['job_skill_points', 'optional', 'number'], ['job_skill_earned', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        if (array_key_exists('job_skill_earned', $params))
        {
            $params['job_skill_points'] = '';
            if (!array_key_exists('status', $params))
            {
                $params['status'] = '';
            }
        }

        $fields = $this->getStudentSkillFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        $result = $query->result_array();

        if (array_key_exists('job_skill_earned', $params))
        {
            $this->load->helper('Grade_Helper');
            $grade_helper = new Grade_Helper();

            foreach ($result as $key => $element)
            {
                $status = $element['skill_status'];
                $points = (int)$element['job_skill_points'];
                $earned = (string)$grade_helper->GetEarned($points, $status);
                $result[$key]['job_skill_earned'] = $earned;
            }
        }

        return ($result);
    }

    public function putStudentSkill($params, $checkGoals = true)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['skill_id', 'mandatory', 'number'],
            ['job_id', 'mandatory', 'number'], ['status', 'mandatory', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->load->helper('Grade_Helper');
        $grade_helper = new Grade_Helper();
        if ($grade_helper->ExistingGrade($params['status']) == false)
        {
            return ($this->Status()->PreconditionFailed());
        }

        // Recuperation de l'id du acquiered_skill
        $this->db->select('acquiered_skill.id');
        $this->db->join('job_skill', 'acquiered_skill.job_skill_fk=job_skill.id');
        $this->db->where('acquiered_skill.student_fk', $params['student_id']);
        $this->db->where('job_skill.job_fk', $params['job_id']);
        $this->db->where('job_skill.skill_fk', $params['skill_id']);
        $query = $this->db->get($this->table);
        $result = $query->result_array();

        if (count($result) != 1)
        {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $data['status'] = $params['status'];

        $this->db->where('id', $result[0]['id']);
        $response = $this->db->update($this->table, $data);

        if ($checkGoals) {
            $this->load->model('Unit_Goal_Model');

            $data_check_unit_goal = [
                "student_id" => $params['student_id'],
            ];

            $this->Unit_Goal_Model->getUnitGoalStudent($data_check_unit_goal);
        }

        return ($response);
    }

    public function getStudentSkillTotal($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['skill_id', 'optional', 'number'],
            ['skill_name', 'optional', 'string'], ['class_id', 'optional', 'number'],
            ['class_name', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->select('acquiered_skill.status');
        $this->db->select('skill.id as skill_id');
        $this->db->select('job_skill.earned');
        $this->db->join('job_skill','acquiered_skill.job_skill_fk= job_skill.id');
        $this->db->join('skill','skill.id=job_skill.skill_fk');
        $this->db->join('class','skill.class_fk = class.id');
        $this->db->order_by('skill.name', 'ASC');
        $this->db->where('acquiered_skill.student_fk', $params['student_id']);
    
        if (array_key_exists('skill_name', $params))
        {
            $this->db->select('skill.name as skill_name');
            if (strlen($params['skill_name']) > 0)
            {
                $this->db->like('skill.name', $params['skill_name']);
            }
        }
        if (array_key_exists('class_id', $params))
        {
            $this->db->select('class.id as class_id');
            if (strlen($params['class_id']) > 0)
            {
                $this->db->where('class.id', $params['class_id']);
            }
        }
        if (array_key_exists('class_name', $params))
        {
            $this->db->select('class.name as class_name');
            if (strlen($params['class_name']) > 0)
            {
                $this->db->like('class.name', $params['class_name']);
            }
        }
        if (array_key_exists('skill_id', $params))
        {
            if (strlen($params['skill_id']) > 0)
            {
                $this->db->where('skill.id', $params['skill_id']);
            }
        }

        $query = $this->db->get($this->table);
        $result = $query->result_array();

        $allskills = [];

        $this->load->helper('Grade_Helper');
        $grade_helper = new Grade_Helper();

        foreach ($result as $key => $skill)
        {
            if (!array_key_exists($skill['skill_id'], $allskills))
            {
                $allskills[$skill['skill_id']] = [
                    'skill_id' => $skill['skill_id'],
                    'earned' => 0,
                    'progress' => 0,
                    'total' => 0,
                    'averageT' => 0
                ];
                if (array_key_exists('skill_name', $params))
                {
                    $allskills[$skill['skill_id']]['skill_name'] = $skill['skill_name'];
                }
                if (array_key_exists('class_name', $params))
                {
                    $allskills[$skill['skill_id']]['class_name'] = $skill['class_name'];
                }
                if (array_key_exists('class_id', $params))
                {
                    $allskills[$skill['skill_id']]['class_id'] = $skill['class_id'];
                }
            }
            $points = (int)$skill['earned'];
            if ($skill['status'] == 'En cours')
            {
                $allskills[$skill['skill_id']]['progress'] += (int)$skill['earned'];
            }
            else
            {
                $real = $grade_helper->GetEarned($points, $skill['status']);
                $note = $grade_helper->GetCoefValue($points, $skill['status']);
                $allskills[$skill['skill_id']]['earned'] += $real;
                $allskills[$skill['skill_id']]['total'] += $points;
                $allskills[$skill['skill_id']]['averageT'] += $note;
            }
        }
        
        foreach ($allskills as $key => $skill)
        {
            $allskills[$key]['average'] = 0;
            if ($allskills[$key]['averageT'] > 0)
            {
                $allskills[$key]['average'] = $allskills[$key]['averageT'] / $allskills[$key]['total'];
            }
        }

        if (count($allskills) <= 0)
        {
            return ([]);
        }

        $result = [];
        foreach ($allskills as $skill)
        {
            unset($skill['averageT']);
            $skill['grade'] = $grade_helper->ValueToStatus($skill['average']);
            if ($skill['total'] == 0)
            {
                $skill['grade'] = 'En cours';
            }
            array_push($result, $skill);
        }

        return ($result);
        
    }

    public function getStudentClassTotal($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['class_id', 'optional', 'number'],
            ['class_name', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->select('acquiered_skill.status');
        $this->db->select('skill.id as skill_id');
        $this->db->select('class.id as class_id');
        $this->db->select('job_skill.earned');
        $this->db->join('job_skill','acquiered_skill.job_skill_fk= job_skill.id');
        $this->db->join('skill','skill.id=job_skill.skill_fk');
        $this->db->join('class','skill.class_fk = class.id');
        $this->db->order_by('skill.name', 'ASC');
        $this->db->where('acquiered_skill.student_fk', $params['student_id']);
    
        if (array_key_exists('class_name', $params))
        {
            $this->db->select('class.name as class_name');
            if (strlen($params['class_name']) > 0)
            {
                $this->db->like('class.name', $params['class_name']);
            }
        }
        if (array_key_exists('class_id', $params))
        {
            if (strlen($params['class_id']) > 0)
            {
                $this->db->where('class.id', $params['class_id']);
            }
        }

        $query = $this->db->get($this->table);
        $result = $query->result_array();

        $allskills = [];

        $this->load->helper('Grade_Helper');
        $grade_helper = new Grade_Helper();

        foreach ($result as $key => $skill)
        {
            if (!array_key_exists($skill['class_id'], $allskills))
            {
                $allskills[$skill['class_id']] = [
                    'class_id' => $skill['class_id'],
                    'earned' => 0,
                    'progress' => 0,
                    'total' => 0,
                    'averageT' => 0
                ];
                if (array_key_exists('class_name', $params))
                {
                    $allskills[$skill['class_id']]['class_name'] = $skill['class_name'];
                }
            }
            $points = (int)$skill['earned'];
            if ($skill['status'] == 'En cours')
            {
                $allskills[$skill['class_id']]['progress'] += (int)$skill['earned'];
            }
            else
            {
                $real = $grade_helper->GetEarned($points, $skill['status']);
                $note = $grade_helper->GetCoefValue($points, $skill['status']);
                $allskills[$skill['class_id']]['earned'] += $real;
                $allskills[$skill['class_id']]['total'] += $points;
                $allskills[$skill['class_id']]['averageT'] += $note;
            }
        }
        
        foreach ($allskills as $key => $skill)
        {
            $allskills[$key]['average'] = 0;
            if ($allskills[$key]['averageT'] > 0)
            {
                $allskills[$key]['average'] = $allskills[$key]['averageT'] / $allskills[$key]['total'];
            }
        }

        if (count($allskills) <= 0)
        {
            return ([]);
        }

        $result = [];
        foreach ($allskills as $skill)
        {
            unset($skill['averageT']);
            $skill['grade'] = $grade_helper->ValueToStatus($skill['average']);
            if ($skill['total'] == 0)
            {
                $skill['grade'] = 'En cours';
            }
            array_push($result, $skill);
        }

        return ($result);        
    }

    public function getStudentPromotionClassTotal($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['class_id', 'optional', 'number'],
            ['class_name', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->trans_start();
        $this->db->select('promotion_fk');
        $this->db->join('applicant','applicant.id = student.applicant_fk');
        $this->db->where('student.id', $params['student_id']);
        $query = $this->db->get('student');
        $result = $query->result_array();
        if (count($result) != 1)
        {
            return ($this->Status()->Forbidden());
        }

        $this->db->select('acquiered_skill.status');
        $this->db->select('skill.id as skill_id');
        $this->db->select('class.id as class_id');
        $this->db->select('job_skill.earned');
        $this->db->join('job_skill','acquiered_skill.job_skill_fk = job_skill.id');
        $this->db->join('skill','skill.id = job_skill.skill_fk');
        $this->db->join('class','skill.class_fk = class.id');
        $this->db->join('student', 'acquiered_skill.student_fk = student.id');
        $this->db->join('applicant','applicant.id = student.applicant_fk');
        $this->db->order_by('skill.name', 'ASC');
        $this->db->where('applicant.promotion_fk', $result[0]['promotion_fk']);
    
        if (array_key_exists('class_name', $params))
        {
            $this->db->select('class.name as class_name');
            if (strlen($params['class_name']) > 0)
            {
                $this->db->like('class.name', $params['class_name']);
            }
        }
        if (array_key_exists('class_id', $params))
        {
            if (strlen($params['class_id']) > 0)
            {
                $this->db->where('class.id', $params['class_id']);
            }
        }

        $query = $this->db->get($this->table);
        $result = $query->result_array();
        $this->db->trans_complete();
        
        $allskills = [];

        $this->load->helper('Grade_Helper');
        $grade_helper = new Grade_Helper();

        foreach ($result as $key => $skill)
        {
            if (!array_key_exists($skill['class_id'], $allskills))
            {
                $allskills[$skill['class_id']] = [
                    'class_id' => $skill['class_id'],
                    'total' => 0,
                    'averageT' => 0
                ];
                if (array_key_exists('class_name', $params))
                {
                    $allskills[$skill['class_id']]['class_name'] = $skill['class_name'];
                }
            }
            $points = (int)$skill['earned'];
            if ($skill['status'] != 'En cours')
            {
                $note = $grade_helper->GetCoefValue($points, $skill['status']);
                $allskills[$skill['class_id']]['total'] += $points;
                $allskills[$skill['class_id']]['averageT'] += $note;
            }
        }
        
        foreach ($allskills as $key => $skill)
        {
            $allskills[$key]['average'] = 0;
            if ($allskills[$key]['averageT'] > 0)
            {
                $allskills[$key]['average'] = $allskills[$key]['averageT'] / $allskills[$key]['total'];
            }
        }

        if (count($allskills) <= 0)
        {
            return ([]);
        }

        $result = [];
        foreach ($allskills as $skill)
        {
            unset($skill['averageT']);
            $skill['grade'] = $grade_helper->ValueToStatus($skill['average']);
            if ($skill['total'] == 0)
            {
                $skill['grade'] = 'En cours';
            }
            array_push($result, $skill);
        }

        return ($result);        
    }

    public function getPromotionClassTotal($params)
    {
        $constraints = [
            ['promotion_id', 'mandatory', 'number'], ['class_id', 'optional', 'number'],
            ['class_name', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->select('acquiered_skill.status');
        $this->db->select('skill.id as skill_id');
        $this->db->select('class.id as class_id');
        $this->db->select('job_skill.earned');
        $this->db->join('job_skill','acquiered_skill.job_skill_fk = job_skill.id');
        $this->db->join('skill','skill.id = job_skill.skill_fk');
        $this->db->join('class','skill.class_fk = class.id');
        $this->db->join('student', 'acquiered_skill.student_fk = student.id');
        $this->db->join('applicant','applicant.id = student.applicant_fk');
        $this->db->order_by('skill.name', 'ASC');
        $this->db->where('applicant.promotion_fk', $params['promotion_id']);
    
        if (array_key_exists('class_name', $params))
        {
            $this->db->select('class.name as class_name');
            if (strlen($params['class_name']) > 0)
            {
                $this->db->like('class.name', $params['class_name']);
            }
        }
        if (array_key_exists('class_id', $params))
        {
            if (strlen($params['class_id']) > 0)
            {
                $this->db->where('class.id', $params['class_id']);
            }
        }

        $query = $this->db->get($this->table);
        $result = $query->result_array();
        $this->db->trans_complete();
        
        $allskills = [];

        $this->load->helper('Grade_Helper');
        $grade_helper = new Grade_Helper();

        foreach ($result as $key => $skill)
        {
            if (!array_key_exists($skill['class_id'], $allskills))
            {
                $allskills[$skill['class_id']] = [
                    'class_id' => $skill['class_id'],
                    'total' => 0,
                    'averageT' => 0
                ];
                if (array_key_exists('class_name', $params))
                {
                    $allskills[$skill['class_id']]['class_name'] = $skill['class_name'];
                }
            }
            $points = (int)$skill['earned'];
            if ($skill['status'] != 'En cours')
            {
                $note = $grade_helper->GetCoefValue($points, $skill['status']);
                $allskills[$skill['class_id']]['total'] += $points;
                $allskills[$skill['class_id']]['averageT'] += $note;
            }
        }
        
        foreach ($allskills as $key => $skill)
        {
            $allskills[$key]['average'] = 0;
            if ($allskills[$key]['averageT'] > 0)
            {
                $allskills[$key]['average'] = $allskills[$key]['averageT'] / $allskills[$key]['total'];
            }
        }

        if (count($allskills) <= 0)
        {
            return ([]);
        }

        $result = [];
        foreach ($allskills as $skill)
        {
            unset($skill['averageT']);
            $skill['grade'] = $grade_helper->ValueToStatus($skill['average']);
            if ($skill['total'] == 0)
            {
                $skill['grade'] = 'En cours';
            }
            array_push($result, $skill);
        }

        return ($result);        
    }

    private function getStudentSkillFields()
    {
        return([
            'student_id' => [
                'type' => 'in',
                'field'=> 'student_fk',
                'filter'=>'where',
                'alias'=>'student_id'
            ],
            'skill_id' => [
                'type'=>'out',
                'link'=> [
                    ['left'=>['acquiered_skill','job_skill_fk'], 'right' => ['job_skill','id']]
                ],
                'field'=>'skill_fk',
                'filter'=>'where',
                'alias'=>'skill_id'
            ],
            'skill_name'=> [
                'type'=> 'out',
                'link'=> [
                    ['left'=>['acquiered_skill','job_skill_fk'], 'right' => ['job_skill','id']],
                    ['left'=>['job_skill','skill_fk'], 'right'=>['skill','id']]
                ],
                'field'=>'name',
                'filter'=>'like',
                'alias'=>'skill_name'
            ],
            'class_id'=>[
                'type'=>'out',
                'link'=>[
                    ['left'=>['acquiered_skill','job_skill_fk'], 'right' => ['job_skill','id']],
                    ['left'=>['job_skill','skill_fk'], 'right'=>['skill','id']]
                ],
                'field'=>'class_fk',
                'alias'=>'class_id',
                'filter'=>'where'
            ],
            'class_name'=>[
                'type'=>'out',
                'link'=>[
                    ['left'=>['acquiered_skill','job_skill_fk'], 'right' => ['job_skill','id']],
                    ['left'=>['job_skill','skill_fk'], 'right'=>['skill','id']],
                    ['left'=>['skill','class_fk'], 'right'=>['class','id']],
                ],
                'field'=>'name',
                'filter'=>'where'
            ],
            'job_id'=>[
                'type'=>'out',
                'link'=>[
                    ['left'=>['acquiered_skill','registration_fk'], 'right'=>['registration','id']]
                ],
                'field'=>'job_fk',
                'alias'=>'job_id',
                'filter'=>'where'
            ],
            'job_name'=>[
                'type'=>'out',
                'link'=>[
                    ['left'=>['acquiered_skill','registration_fk'], 'right'=>['registration','id']],
                    ['left'=>['registration','job_fk'], 'right'=>['job','id']]
                ],
                'field'=>'name',
                'filter'=>'like'
            ],
            'group_id'=>[
                'type'=>'out',
                'link'=>[
                    ['left'=>['acquiered_skill','registration_fk'], 'right'=>['registration','id']]
                ],
                'field'=>'group_id',
                'filter'=>'where',
                'alias'=>'group_id'
            ],
            'group_name'=>[
                'type'=>'out',
                'link'=>[
                    ['left'=>['acquiered_skill','registration_fk'], 'right'=>['registration','id']]
                ],
                'field'=>'group_name',
                'filter'=>'like',
                'alias'=>'group_name'
            ],
            'unit_id'=>[
                'type'=>'out',
                'link'=>[
                    ['left'=>['acquiered_skill','registration_fk'], 'right'=>['registration','id']],
                    ['left'=>['registration','job_fk'], 'right'=>['job','id']],
                    ['left'=>['job','unit_fk'], 'right'=>['unit','id']]
                ],
                'field'=>'id',
                'alias' => 'unit_id',
                'filter'=>'where'
            ],
            'unit_name'=>[
                'type'=>'out',
                'link'=>[
                    ['left'=>['acquiered_skill','registration_fk'], 'right'=>['registration','id']],
                    ['left'=>['registration','job_fk'], 'right'=>['job','id']],
                    ['left'=>['job','unit_fk'], 'right'=>['unit','id']]
                ],
                'field'=>'name',
                'filter'=>'like'
            ],
            'status'=>[
                'type'=>'in',
                'field'=>'status',
                'filter'=>'like',
                'alias'=>'skill_status'
            ],
            'job_skill_points'=> [
                'type'=>'out',
                'link'=>[
                    ['left'=>['acquiered_skill','job_skill_fk'], 'right'=>['job_skill','id']]
                ],
                'field'=>'earned',
                'alias' => 'job_skill_points',
                'filter'=>'where'
            ]
        ]);
    }
}

?>