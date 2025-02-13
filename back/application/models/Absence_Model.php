<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Absence_Model extends LPTF_Model
{
    private $table = 'absence';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);

    }

    public function getAbsence($params)
    {
        $constraints = [
            ['id', 'optional', 'number'], ['student_id', 'optional', 'number'], ['start_date', 'optional', 'string'],
            ['end_date', 'optional', 'string'], ['duration', 'optional', 'number'], ['validator', 'optional', 'string'],
            ['status', 'optional', 'number'], ['link', 'optional', 'string'], ['comment', 'optional', 'string'], 
            ['reason', 'optional', 'string'], 
            ['student_email', 'optional', 'string'],['date_before', 'optional', 'string'],
            ['date_after', 'optional', 'string'], ['current_unit_id', 'optional', 'number', true],
            ['promotion_name', 'optional', 'string'], ['promotion_id', 'optional', 'number'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getAbsenceFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        $absences_arr = $query->result_array();


        return ($absences_arr);
    }

    public function postAbsence($params)
    {

        $constraints = [
            ['student_id', 'mandatory', 'number'], ['start_date', 'mandatory', 'string'],
            ['end_date', 'mandatory', 'string'], ['duration', 'mandatory', 'number'], ['email', 'mandatory', 'string'],
            ['status', 'optional', 'number'], ['reason', 'mandatory', 'string'], 
            ['image', 'mandatory', 'string'], ['fileType', 'mandatory', 'string'], ['imageName', 'mandatory', 'string'],     
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }
        
        $acceptedTypes = ["image/png", "image/jpeg", "image/jpg", "application/pdf"];
        if(!in_array($params['fileType'], $acceptedTypes)) return ($this->Status()->PreconditionFailed());
        
        $img = str_replace('[removed]', '', $params['image']);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);

        require_once './application/helpers/google_drive_helper.php';
        $GDrive = new Google_Drive_Helper();

        $upload = $GDrive->uploadBasic($data, $params['imageName'], $params['fileType'], GOOGLE_DRIVE_ABSENCE_FOLDER);
        if(!$upload) return ($this->Status()->Error()); 

        $data = [
            'student_fk' => $params['student_id'],
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date'],
            'duration' => $params['duration'],
            'link' => $upload,
            'reason' => $params['reason'],
        ];

        $response = $this->db->insert($this->table, $data);

        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putAbsence($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number'], ['student_id', 'optional', 'number'], ['start_date', 'optional', 'string'],
            ['end_date', 'optional', 'string'], ['duration', 'optional', 'number'], ['validator', 'optional', 'string'],
            ['status', 'optional', 'number'], ['link', 'optional', 'string'], ['reason', 'optional', 'string'],
            ['comment', 'optional', 'string'], 
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['start_date', 'start_date'],
            ['end_date', 'end_date'],
            ['duration', 'duration'],
            ['validator', 'validator'],
            ['status', 'status'],
            ['link', 'link'],
            ['reason', 'reason'],
            ['comment', 'comment'],
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        if (count($data) > 0) {
            $this->db->where('id', $params['id']);
            $response = $this->db->update($this->table, $data);
        } else {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteAbsence($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->where_in('id', $params['id']);
        $response = $this->db->delete($this->table);

        return ($response);
    }

    private function getAbsenceFields()
    {
        return ([
            'id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'student_id' => [
                'type' => 'in',
                'field' => 'student_fk',
                'alias' => 'student_id',
                'filter' => 'where'
            ],
            'start_date' => [
                'type' => 'in',
                'field' => 'start_date',
                'alias' => 'absence_start_date',
                'filter' => 'where'
            ],
            'end_date' => [
                'type' => 'in',
                'field' => 'end_date',
                'alias' => 'absence_end_date',
                'filter' => 'where'
            ],
            'duration' => [
                'type' => 'in',
                'field' => 'duration',
                'filter' => 'where'
            ],
            'validator' => [
                'type' => 'in',
                'field' => 'validator',
                'filter' => 'where'
            ],
            'status' => [
                'type' => 'in',
                'field' => 'status',
                'filter' => 'where'
            ],
            'link' => [
                'type' => 'in',
                'field' => 'link',
                'filter' => 'where'
            ],
            'reason' => [
                'type' => 'in',
                'field' => 'reason',
                'filter' => 'where'
            ],
            'comment' => [
                'type' => 'in',
                'field' => 'comment',
                'filter' => 'where'
            ],
            'student_email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['absence', 'student_fk'], 'right' => ['student', 'id']]
                ],
                'field' => 'email',
                'alias' => 'student_email',
                'filter' => 'where'
            ],
            'date_after' => [
                'type' => 'filter',
                'field' => 'start_date >=',
                'filter' => 'where'
            ],
            'date_before' => [
                'type' => 'filter',
                'field' => 'end_date <=',
                'filter' => 'where'
            ],
            'current_unit_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['absence', 'student_fk'], 'right' => ['student', 'id']],
                ],
                'alias' => 'current_unit_id',
                'field' => 'current_unit_fk',
                'filter' => 'where',
            ],
            'promotion_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['absence', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'name',
                'alias' => 'promotion_name',
                'filter' => 'where'
            ],
            'promotion_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['absence', 'student_fk'], 'right' => ['student', 'id']],
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
