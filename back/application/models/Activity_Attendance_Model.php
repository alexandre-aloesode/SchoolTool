<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Activity_Attendance_Model extends LPTF_Model
{
    private $table = 'activity_attendance';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getActivityAttendance($params)
    {
        $constraints = [
            ['activity_attendance_id', 'optional', 'number', true], ['student_id', 'optional', 'string'],
            ['activity_id', 'optional', 'number', true], ['is_present', 'optional', 'boolean'],
            ['student_email', 'optional', 'string'], ['unit_id', 'optional', 'number'],
            ['activity_type', 'optional', 'string'], ['activity_date', 'optional', 'string'],
            ['activity_is_mandatory', 'optional', 'boolean'], ['promotion_id', 'optional', 'string', true],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getActivityAttendanceFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postActivityAttendance($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'],
            ['activity_id', 'mandatory', 'number'], ['is_present', 'mandatory', 'boolean']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [
            'student_fk' => $params['student_id'],
            'activity_fk' => $params['activity_id'],
            'is_present' => $params['is_present'],
        ];

        $response = $this->db->insert($this->table, $data);

        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putActivityAttendance($params)
    {
        $constraints = [
            ['activity_attendance_id', 'mandatory', 'number'], ['is_present', 'optional', 'boolean']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['is_present', 'is_present']
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        if (count($data) > 0) {
            $this->db->where('id', $params['activity_attendance_id']);
            $response = $this->db->update($this->table, $data);
        } else {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    private function getActivityAttendanceFields()
    {
        return ([
            'activity_attendance_id' => [
                'type' => 'in',
                'field' => 'id',
                'alias' => 'activity_attendance_id',
                'filter' => 'where'
            ],
            'student_id' => [
                'type' => 'in',
                'field' => 'student_fk',
                'alias' => 'student_id',
                'filter' => 'where'
            ],
            'activity_id' => [
                'type' => 'in',
                'field' => 'activity_fk',
                'alias' => 'activity_id',
                'filter' => 'where'
            ],
            'is_present' => [
                'type' => 'in',
                'field' => 'is_present',
                'alias' => 'is_present',
                'filter' => 'where'
            ],
            'student_email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['activity_attendance', 'student_fk'], 'right' => ['student', 'id']]
                ],
                'field' => 'email',
                'alias' => 'student_email',
                'filter' => 'where',
            ],
            'unit_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['activity_attendance', 'activity_fk'], 'right' => ['activity', 'id']]
                ],
                'field' => 'unit_fk',
                'alias' => 'unit_id',
                'filter' => 'where'
            ],
            'activity_type' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['activity_attendance', 'activity_fk'], 'right' => ['activity', 'id']]
                ],
                'field' => 'type',
                'alias' => 'activity_type',
                'filter' => 'where'
            ],
            'activity_date' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['activity_attendance', 'activity_fk'], 'right' => ['activity', 'id']]
                ],
                'field' => 'date',
                'alias' => 'activity_date',
                'filter' => 'where'
            ],
            'activity_is_mandatory' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['activity_attendance', 'activity_fk'], 'right' => ['activity', 'id']]
                ],
                'field' => 'is_mandatory',
                'alias' => 'activity_is_mandatory',
                'filter' => 'where'
            ],
            'promotion_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['activity_attendance', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'id',
                'alias' => 'promotion_id',
                'filter' => 'where'
            ],
        ]);
    }
}
