<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Unit_Viewer_Model extends LPTF_Model
{
    private $table = 'unit_viewer';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getUnitStudent($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number', true], ['student_id', 'optional', 'number'],
            ['student_firstname', 'optional', 'string'], ['student_lastname', 'optional', 'string'],
            ['student_email', 'optional', 'string'], ['student_current_unit_id', 'optional', 'number'],
            ['student_current_unit_name', 'optional', 'string'], ['student_current_unit_code', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getUnitStudentFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postUnitStudent($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number'], ['student_id', 'mandatory', 'number']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [
            'unit_fk' => $params['unit_id'],
            'student_fk' => $params['student_id']
        ];

        $student_units = $this->getUnitStudent($params);
        if (count($student_units) === 0) {
            $response = $this->db->insert($this->table, $data);

            $data_unit_history = [
                'unit_id' => $params['unit_id'],
                'student_id' => $params['student_id']
            ];

            $this->load->model('Unit_History_Model');
            $this->Unit_History_Model->postUnitHistory($data_unit_history);
        }

        return ($response === true ? $this->db->insert_id() : false);
    }

    public function deleteUnitStudent($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number'], ['student_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->trans_start();
        $this->load->model('Waiting_List_Model');
        $this->Waiting_List_Model->deleteUnitWaitingList($params);

        $this->db->where('student_fk', $params['student_id']);
        $this->db->where("unit_fk", $params["unit_id"]);
        $response = $this->db->delete($this->table);
        $this->db->trans_complete();

        return ($response);
    }

    public function postUnitStudents($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number'], ['student_id', 'mandatory', 'array', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $data['unit_id'] = $params['unit_id'];
        foreach ($params['student_id'] as $student) {
            $data['student_id'] = $student;
            $this->postUnitStudent($data);
            if (!$this->Status()->IsValid()) {
                return ($this->Status()->Forbidden());
            }
        }

        return (true);
    }

    public function deleteUnitStudents($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number'], ['student_id', 'mandatory', 'array', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->trans_start();
        $this->load->model('Waiting_List_Model');
        foreach ($params['student_id'] as $student) {
            $data = [
                'unit_id' => $params['unit_id'],
                'student_id' => $student
            ];
            $this->Waiting_List_Model->deleteUnitWaitingList($data);
        }

        $this->db->where_in('student_fk', $params['student_id']);
        $this->db->where("unit_fk", $params["unit_id"]);
        $response = $this->db->delete($this->table);
        $this->db->trans_complete();

        return ($response);
    }

    public function postUnitStudentsCurrent($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number'], ['student_id', 'mandatory', 'array', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $data['unit_id'] = $params['unit_id'];

        $this->db->trans_start();
        foreach ($params['student_id'] as $student) {
            $this->load->model('Student_Model');
            $params_student = [
                'student_id' => $student,
                'current_unit_id' => ''
            ];
            $old_current = $this->Student_Model->getStudent($params_student);
            if (!$this->Status()->IsValid() || count($old_current) != 1) {
                return ($this->Status()->Forbidden());
            }

            $todel = [
                'unit_id' => $old_current[0]['current_unit_id'],
                'student_id' => $student
            ];
            $this->deleteUnitStudent($todel);

            $data['student_id'] = $student;
            $this->postUnitStudent($data);
            if (!$this->Status()->IsValid()) {
                return ($this->Status()->Forbidden());
            }
        }

        $data = [
            'current_unit_fk' => $params['unit_id']
        ];
        $this->db->where_in('id', $params['student_id']);
        $response = $this->db->update("student", $data);
        $this->db->trans_complete();

        return ($response);
    }

    public function getStudentUnit($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['unit_id', 'optional', 'number'],
            ['unit_name', 'optional', 'string'], ['unit_code', 'optional', 'string'],
            ['start_date', 'optional', 'none'], ['end_date', 'optional', 'none']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getStudentUnitFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function deleteUnitsCompletedStudents($params)
    {
        // je suis ici
        $constraints = [
            ['unit_id', 'mandatory', 'number']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->trans_start();

        $this->load->model('Unit_Completed_Model');
        $unit_completed_data = [
            'unit_id' => $params['unit_id'],
            'student_id' => ''
        ];
        $unit_completed = $this->Unit_Completed_Model->getUnitCompleted($unit_completed_data);

        $students_to_del = [];

        foreach ($unit_completed as $unit) {
            array_push($students_to_del, $unit['student_id']);
        }

        $data = [
            'unit_id' => $params['unit_id'],
            'student_id' => $students_to_del
        ];
        $response = $this->deleteUnitStudents($data);

        $this->db->trans_complete();

        return ($response);
    }

    private function getUnitStudentFields()
    {
        return ([
            'unit_id' => [
                'type' => 'in',
                'field' => 'unit_fk',
                'alias' => 'unit_id',
                'filter' => 'where'
            ],
            'student_id' => [
                'type' => 'int',
                'field' => 'student_fk',
                'alias' => 'student_id',
                'filter' => 'where',
            ],
            'student_firstname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_viewer', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' => 'firstname',
                'alias' => 'student_firstname',
                'filter' => 'like',
            ],
            'student_lastname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_viewer', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' => 'lastname',
                'alias' => 'student_lastname',
                'filter' => 'like',
            ],
            'student_email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_viewer', 'student_fk'], 'right' => ['student', 'id']]
                ],
                'field' => 'email',
                'filter' => 'like',
            ],
            'student_current_unit_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_viewer', 'student_fk'], 'right' => ['student', 'id']]
                ],
                'field' => 'current_unit_fk',
                'alias' => 'student_current_unit_id',
                'filter' => 'like'
            ],
            'student_current_unit_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_viewer', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'current_unit_fk'], 'right' => ['unit', 'id']]
                ],
                'field' => 'name',
                'alias' => 'student_current_unit_name',
                'filter' => 'like',
            ],
            'student_current_unit_code' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_viewer', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'current_unit_fk'], 'right' => ['unit', 'id']]
                ],
                'field' => 'code',
                'alias' => 'student_current_unit_code',
                'filter' => 'like',
            ]
        ]);
    }

    private function getStudentUnitFields()
    {
        return ([
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
                'filter' => 'where',
            ],
            'unit_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_viewer', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'field' => 'name',
                'filter' => 'like',
            ],
            'unit_code' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_viewer', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'field' => 'code',
                'filter' => 'like',
            ],
            'start_date' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_viewer', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'field' => 'start_date',
                'filter' => 'none',
            ],
            'end_date' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_viewer', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'field' => 'end_date',
                'filter' => 'none',
            ]
        ]);
    }
}
