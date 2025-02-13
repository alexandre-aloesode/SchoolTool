<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Job_Model extends LPTF_Model
{
    private $table = 'job';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getJob($params)
    {
        $constraints = [
            ['job_id', 'optional', 'number'], ['job_name', 'optional', 'string'],
            ['job_code', 'optional', 'string'], ['duration', 'optional', 'number'],
            ['min_students', 'optional', 'number'], ['max_students', 'optional', 'number'],
            ['link_subject', 'optional', 'string'], ['link_tutor_guide', 'optional', 'string'],
            ['description', 'optional', 'string'], ['is_visible', 'optional', 'boolean'],
            ['unit_id', 'optional', 'number', true], ['unit_name', 'optional', 'string'],
            ['unit_code', 'optional', 'string'], ['unit_is_active', 'optional', 'boolean']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->GetJobFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);
        return ($query->result_array());
    }

    public function postJob($params)
    {
        $constraints = [
            ['job_name', 'mandatory', 'string'], ['unit_id', 'mandatory', 'number'],
            ['job_code', 'mandatory', 'string'], ['duration', 'mandatory', 'number'],
            ['min_students', 'optional', 'number'], ['max_students', 'optional', 'number'],
            ['link_subject', 'optional', 'string'], ['link_tutor_guide', 'optional', 'string'],
            ['description', 'optional', 'string'], ['is_visible', 'optional', 'boolean']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        if ($params["duration"] <= 0) {
            return ($this->Status()->PreconditionFailed());
        }

        $params['job_name'] = strtolower($params['job_name']);
        $params['job_name'][0] = strtoupper($params['job_name'][0]);

        if (!isset($params['min_students']) || $params['min_students'] < 1) {
            $params['min_students'] = 1;
        }
        if (!isset($params['max_students']) || $params['max_students'] < 1) {
            $params['max_students'] = 1;
        }
        if ($params['max_students'] < $params['min_students']) {
            $params['max_students'] = $params['min_students'];
        }

        $data = [
            'name' => $params['job_name'],
            'code' => $params['job_code'],
            'duration' => $params['duration'],
            'unit_fk' => $params['unit_id']
        ];

        $optional_fields = [
            ['min_students', 'min_students'],
            ['max_students', 'max_students'],
            ['link_subject', 'link_subject'],
            ['link_tutor_guide', 'link_tutor_guide'],
            ['description', 'description'],
            ['is_visible', 'is_visible']
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        $response = $this->db->insert($this->table, $data);

        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putJob($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number'], ['job_name', 'optional', 'string'],
            ['duration', 'optional', 'number'], ['min_students', 'optional', 'number'],
            ['max_students', 'optional', 'number'], ['link_subject', 'optional', 'string'],
            ['link_tutor_guide', 'optional', 'string'], ['description', 'optional', 'string'],
            ['is_visible', 'optional', 'boolean'], ['unit_id', 'optional', 'number'],
            ['job_code', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        if (isset($params["duration"]) && $params["duration"] <= 0) {
            return ($this->Status()->PreconditionFailed());
        }

        if (isset($params['min_students']) && $params['min_students'] < 1) {
            $params['min_students'] = 1;
        }
        if (isset($params['max_students']) && $params['max_students'] < 1) {
            $params['max_students'] = 1;
        }
        if (isset($params['max_students']) && $params['max_students'] < $params['min_students']) {
            $params['max_students'] = $params['min_students'];
        }

        if (isset($params['job_name'])) {
            $params['job_name'] = strtolower($params['job_name']);
            $params['job_name'][0] = strtoupper($params['job_name'][0]);
        }

        $data = [];
        $optional_fields = [
            ['name', 'job_name'],
            ['code', 'job_code'],
            ['duration', 'duration'],
            ['min_students', 'min_students'],
            ['max_students', 'max_students'],
            ['link_subject', 'link_subject'],
            ['link_tutor_guide', 'link_tutor_guide'],
            ['description', 'description'],
            ['is_visible', 'is_visible'],
            ['unit_fk', 'unit_id'],
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        if (count($data) > 0) {
            $this->db->where('id', $params['job_id']);
            $response = $this->db->update($this->table, $data);
        } else {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteJob($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->where_in('id', $params['job_id']);
        $response = $this->db->delete($this->table);

        return ($response);
    }

    public function getStudentJobAvailable($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['unit_name', 'optional', 'string'],
            ['unit_id', 'optional', 'number'], ['job_name', 'optional', 'string'],
            ['job_id', 'optional', 'number'], ['job_code', 'optional', 'string'],
            ['job_description', 'optional', 'string'], ['job_duration', 'optional', 'number'],
            ['job_min_students', 'optional', 'number'], ['job_max_students', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->load->model('Acquiered_Skill_Model');
        $skills = $this->Acquiered_Skill_Model->getStudentSkillTotal($params);

        $this->db->select('job_fk');
        $this->db->from('registration');
        $this->db->where('registration.member_fk', $params['student_id']);
        $sub_query = $this->db->get_compiled_select();

        $this->db->select('job.name as job_name, job.id as job_id');
        $this->db->select('unit.name as job_unit_name, unit.id as job_unit_id, unit.code as job_unit_code');
        $this->db->join('unit', 'unit.id=job.unit_fk');
        $this->db->join('unit_viewer', 'unit_viewer.unit_fk=unit.id');
        $this->db->where('unit_viewer.student_fk', $params['student_id']);
        $this->db->where("job.id not in ($sub_query)");
        $this->db->where("job.is_visible", 1);

        if (array_key_exists('unit_name', $params) && strlen($params['unit_name']))
            $this->db->like('unit.name', $params['unit_name']);

        if (array_key_exists('unit_id', $params) && strlen($params['unit_id']))
            $this->db->where('unit.id', $params['unit_id']);

        if (array_key_exists('job_name', $params) && strlen($params['job_name']))
            $this->db->like('job.name', $params['job_name']);

        if (array_key_exists('job_id', $params) && strlen($params['job_id']))
            $this->db->where('job.id', $params['job_id']);


        $optional_fields = [
            'job_code' => 'job.code as job_code',
            'job_description' => 'job.description as job_description',
            'job_duration' => 'job.duration as job_duration',
            'job_min_students' => 'min_students as job_min_students',
            'job_max_students' => 'max_students as job_max_students'
        ];

        foreach ($optional_fields as $key => $field) {
            if (array_key_exists($key, $params)) {
                $this->db->select($field);
            }
        }

        $query = $this->db->get($this->table);
        $jobs = $query->result_array();

        if (count($jobs) <= 0) {
            return ([]);
        }

        $alljobs = '(';
        foreach ($jobs as $key => $job) {
            $alljobs = $alljobs . $job['job_id'] . ',';
        }
        $alljobs[strlen($alljobs) - 1] = ')';

        $this->db->select('job.id as job_id, job_skill.skill_fk, job_skill.needed');
        $this->db->join('job_skill', 'job_skill.job_fk = job.id');
        $this->db->where("job_skill.needed > 0");
        $this->db->where("job.id in $alljobs");

        $query = $this->db->get($this->table);
        $job_skills = $query->result_array();

        foreach ($jobs as &$job) {
            $job['prerequisites'] = 1;
        }

        foreach ($job_skills as $job_skill) {

            if (intval($job_skill['needed']) > 0) {
                $found = false;
                $enough = true;
                foreach ($skills as $skill) {
                    if (intval($skill['skill_id']) == intval($job_skill['skill_fk'])) {
                        $found = true;
                        if (intval($skill['earned']) < intval($job_skill['needed'])) {
                            $enough = false;
                        }
                    }
                }
                if ($enough == false || $found == false) {
                    foreach ($jobs as &$job) {
                        if ((intval($job['job_id']) == intval($job_skill['job_id']))) {
                            $job['prerequisites'] = 0;
                        }
                    }
                }
            }
        }

        return ($jobs);
    }

    public function getJobStudentAvailable($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $jobsfilter = [
            'job_id' => $params["job_id"],
            'unit_id' => ''
        ];
        $jobInfos = $this->getJob($jobsfilter);
        $this->db->trans_start();
        $this->db->select('r.member_fk');
        $this->db->from('registration as r');
        $this->db->where("r.job_fk ='" . $params["job_id"] . "'");
        $sub_query = $this->db->get_compiled_select();

        $this->db->select('s.id as student_id, s.email as student_email, a.firstname as student_firstname, a.lastname as student_lastname');
        $this->db->from('unit_viewer as uv');
        $this->db->join('student as s', 's.id=uv.student_fk', "inner");
        $this->db->join('applicant as a', 'a.id=s.applicant_fk', "inner");
        $this->db->where("uv.unit_fk = '" . $jobInfos[0]["unit_id"] . "'");
        $this->db->where("uv.student_fk not in ($sub_query)");
        $this->db->order_by('student_email', 'asc');
        $query = $this->db->get();
        $students = $query->result_array();
        $this->db->trans_complete();

        return ($students);
    }

    private function getJobFields()
    {
        return ([
            'job_id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'job_name' => [
                'type' => 'in',
                'field' => 'name',
                'alias' => 'job_name',
                'filter' => 'like'
            ],
            'job_code' => [
                'type' => 'in',
                'field' => 'code',
                'alias' => 'job_code',
                'filter' => 'like',
            ],
            'duration' => [
                'type' => 'in',
                'field' => 'duration',
                'filter' => 'where',
            ],
            'min_students' => [
                'type' => 'in',
                'field' => 'min_students',
                'filter' => 'where',
            ],
            'max_students' => [
                'type' => 'in',
                'field' => 'max_students',
                'filter' => 'where',
            ],
            'link_subject' => [
                'type' => 'in',
                'field' => 'link_subject',
                'filter' => 'none',
            ],
            'link_tutor_guide' => [
                'type' => 'in',
                'field' => 'link_tutor_guide',
                'filter' => 'none',
            ],
            'description' => [
                'type' => 'in',
                'field' => 'description',
                'filter' => 'none',
            ],
            'is_visible' => [
                'type' => 'in',
                'field' => 'is_visible',
                'filter' => 'where',
            ],
            'unit_id' => [
                'type' => 'in',
                'field' => 'unit_fk',
                'alias' => 'unit_id',
                'filter' => 'where',
            ],
            'unit_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['job', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'field' => 'name',
                'filter' => 'like',
            ],
            'unit_is_active' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['job', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'field' => 'is_active',
                'filter' => 'where',
            ],
            'unit_code' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['job', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'field' => 'code',
                'alias' => 'unit_code',
                'filter' => 'like',
            ]
        ]);
    }
}
