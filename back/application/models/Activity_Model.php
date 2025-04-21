<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Activity_Model extends LPTF_Model
{
    private $table = 'activity';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getActivity($params)
    {
        $constraints = [
            ['activity_id', 'optional', 'number', true], ['activity_type', 'optional', 'string'],
            ['unit_id', 'optional', 'number', true], ['is_mandatory', 'optional', 'boolean'], ['author', 'optional', 'string'],
            ['date', 'optional', 'string'], ['comment', 'optional', 'string'], ['attendance', 'optional', 'string'],
        ];

        $attendance = false;
        if (isset($params['attendance']) && $params['attendance'] == '') {
            $attendance = true;
        }

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getActivityFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);
        $activities = $query->result_array();

        if ($attendance == true) {
            $this->load->model('Activity_Attendance_Model');
            foreach ($activities as $key => $activity) {
                $activities[$key]['attendance'] = $this->Activity_Attendance_Model->getActivityAttendance([
                    'activity_attendance_id' => '',
                    'activity_id' => $activity['activity_id'],
                    'student_id' => '',
                    'is_present' => '',
                    'student_email' => '',
                ]);
            }
        }

        return ($activities);
    }

    public function postActivity($params)
    {
        $constraints = [
            ['present_students', 'mandatory', 'string', true],
            ['activity_type', 'mandatory', 'string'], ['is_mandatory', 'mandatory', 'boolean'],
            ['unit_id', 'mandatory', 'number'], ['author', 'mandatory', 'string'], ['comment', 'optional', 'string'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $email = $this->token_helper->get_payload()['user_email'];
        if ($email === null) return ($this->Status()->ExpectationFailed());

        $data = [
            'type' => $params['activity_type'],
            'is_mandatory' => $params['is_mandatory'],
            'unit_fk' => $params['unit_id'],
            'author' => $email,
            'date' => date('Y-m-d'),
        ];

        $optional_fields = [
            ['comment', 'comment'],
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }
        $this->db->trans_start();
        $response = $this->db->insert($this->table, $data);
        $activity_id = $this->db->insert_id();

        $this->load->model('Unit_Viewer_Model');
        $unit_students = $this->Unit_Viewer_Model->getUnitStudent([
            'unit_id' => $params['unit_id'],
            'student_id' => '',
            'student_email' => '',
        ]);

        if (count($unit_students) > 0) {
            $this->load->model('Activity_Attendance_Model');
            foreach ($unit_students as $student) {
                $this->Activity_Attendance_Model->postActivityAttendance([
                    'student_id' => (string)$student['student_id'],
                    'activity_id' => (string)$activity_id,
                    'is_present' => in_array($student['student_email'], $params['present_students']) ? "1" : "0",
                ]);
            }
        }
        $this->db->trans_complete();

        return ($response === true ? $activity_id : false);
    }

    public function putActivity($params)
    {
        $constraints = [
            ['activity_id', 'mandatory', 'number'], ['activity_type', 'optional', 'string'],
            ['unit_id', 'optional', 'number', true], ['is_mandatory', 'optional', 'boolean'],
            ['author', 'optional', 'string'], ['comment', 'optional', 'string'], ['date', 'optional', 'string'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['type', 'activity_type'], ['unit_fk', 'unit_id'], ['is_mandatory', 'is_mandatory'],
            ['author', 'author'], ['comment', 'comment'], ['date', 'date']
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        if (count($data) > 0) {
            $this->db->where('id', $params['activity_id']);
            $response = $this->db->update($this->table, $data);
        } else {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteActivity($params)
    {
        $constraints = [
            ['activity_id', 'mandatory', 'number'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->where('id', $params['activity_id']);
        $response = $this->db->delete($this->table);

        return ($response);
    }

    private function getActivityFields()
    {
        return ([
            'activity_id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'activity_type' => [
                'type' => 'in',
                'field' => 'type',
                'filter' => 'where'
            ],
            'unit_id' => [
                'type' => 'in',
                'alias' => 'unit_id',
                'field' => 'unit_fk',
                'filter' => 'where'
            ],
            'is_mandatory' => [
                'type' => 'in',
                'field' => 'is_mandatory',
                'filter' => 'where'
            ],
            'author' => [
                'type' => 'in',
                'field' => 'author',
                'filter' => 'where'
            ],
            'date' => [
                'type' => 'in',
                'field' => 'date',
                'filter' => 'where'
            ],
            'comment' => [
                'type' => 'in',
                'field' => 'comment',
                'filter' => 'where'
            ],

        ]);
    }
}
