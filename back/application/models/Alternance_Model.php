<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alternance_Model extends LPTF_Model
{
    private $table = 'alternance';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getAlternance($params)
    {
        $constraints = [
            ['id', 'optional', 'number'],
            ['student_id', 'optional', 'number'],
            ['start_date', 'optional', 'string'],
            ['end_date', 'optional', 'string'],
            ['company', 'optional', 'string'],
            ['contract_type', 'optional', 'string'],
            ['payment_for', 'optional', 'string'],
            ['cost_per_hour', 'optional', 'number'],
            ['OPCO', 'optional', 'string'],
            ['OPCO_number', 'optional', 'number'],
            ['DRETS_number', 'optional', 'number'],
            ['tutor_firstname', 'optional', 'string'],
            ['tutor_lastname', 'optional', 'string'],
            ['tutor_email', 'optional', 'string'],
            ['student_firstname', 'optional', 'string'],
            ['student_lastname', 'optional', 'string'],
            ['student_email', 'optional', 'string'],
            ['student_id', 'optional', 'number'],
            ['promotion_id', 'optional', 'number', true],
            ['promotion_name', 'optional', 'string'],
            ['promotion_is_active', 'optional', 'string'],
            ['promotion_certification', 'optional', 'string'],
            ['current_unit_id', 'optional', 'number', true],
            ['status', 'optional', 'string'],
            ['filiz_folder_id', 'optional', 'string', true],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getAlternanceFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        $alternances_arr = $query->result_array();


        return ($alternances_arr);
    }

    public function postAlternance($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'],
            ['start_date', 'optional', 'string'],
            ['end_date', 'optional', 'string'],
            ['company', 'optional', 'string'],
            ['contract_type', 'optional', 'string'],
            ['payment_for', 'optional', 'string'],
            ['cost_per_hour', 'optional', 'number'],
            ['OPCO', 'optional', 'string'],
            ['OPCO_number', 'optional', 'string'],
            ['DRETS_number', 'optional', 'number'],
            ['tutor_firstname', 'optional', 'string'],
            ['tutor_lastname', 'optional', 'string'],
            ['tutor_email', 'optional', 'string'],
            ['filiz_folder_id', 'optional', 'string'],
            ['status', 'optional', 'string'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->load->model('Student_Model');
        $check_if_student_exists = $this->Student_Model->getStudent(['student_id' => $params['student_id']]);
        if (count($check_if_student_exists) == 0) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [
            'student_fk' => $params['student_id']
        ];

        $optional_fields = [
            ['contract_type', 'contract_type'],
            ['OPCO', 'OPCO'],
            ['start_date', 'start_date'],
            ['end_date', 'end_date'],
            ['payment_for', 'payment_for'],
            ['cost_per_hour', 'cost_per_hour'],
            ['OPCO_number', 'OPCO_number'],
            ['DRETS_number', 'DRETS_number'],
            ['company', 'company'],
            ['tutor_firstname', 'tutor_firstname'],
            ['tutor_lastname', 'tutor_lastname'],
            ['tutor_email', 'tutor_email'],
            ['filiz_folder_id', 'filiz_folder_id'],
            ['status', 'status'],
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        $response = $this->db->insert($this->table, $data);

        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putAlternance($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number'],
            ['student_id', 'optional', 'number'],
            ['start_date', 'optional', 'string'],
            ['end_date', 'optional', 'string'],
            ['company', 'optional', 'string'],
            ['contract_type', 'optional', 'string'],
            ['payment_for', 'optional', 'string'],
            ['cost_per_hour', 'optional', 'number'],
            ['OPCO', 'optional', 'string'],
            ['OPCO_number', 'optional', 'number'],
            ['DRETS_number', 'optional', 'number'],
            ['tutor_firstname', 'optional', 'string'],
            ['tutor_lastname', 'optional', 'string'],
            ['tutor_email', 'optional', 'string'],
            ['filiz_folder_id', 'optional', 'string'],
            ['status', 'optional', 'string'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['OPCO', 'OPCO'],
            ['OPCO_number', 'OPCO_number'],
            ['DRETS_number', 'DRETS_number'],
            ['company', 'company'],
            ['contract_type', 'contract_type'],
            ['payment_for', 'payment_for'],
            ['cost_per_hour', 'cost_per_hour'],
            ['tutor_firstname', 'tutor_firstname'],
            ['tutor_lastname', 'tutor_lastname'],
            ['tutor_email', 'tutor_email'],
            ['start_date', 'start_date'],
            ['end_date', 'end_date'],
            ['student_id', 'student_id'],
            ['filiz_folder_id', 'filiz_folder_id'],
            ['status', 'status'],
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

    public function deleteAlternance($params)
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

    private function getAlternanceFields()
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
            'OPCO' => [
                'type' => 'in',
                'field' => 'OPCO',
                'filter' => 'where'
            ],
            'start_date' => [
                'type' => 'in',
                'field' => 'start_date',
                'filter' => 'where'
            ],
            'end_date' => [
                'type' => 'in',
                'field' => 'end_date',
                'filter' => 'where'
            ],
            'payment_for' => [
                'type' => 'in',
                'field' => 'payment_for',
                'filter' => 'where'
            ],
            'cost_per_hour' => [
                'type' => 'in',
                'field' => 'cost_per_hour',
                'filter' => 'where'
            ],
            'OPCO_number' => [
                'type' => 'in',
                'field' => 'OPCO_number',
                'filter' => 'where'
            ],
            'DRETS_number' => [
                'type' => 'in',
                'field' => 'DRETS_number',
                'filter' => 'where'
            ],
            'contract_type' => [
                'type' => 'in',
                'field' => 'contract_type',
                'filter' => 'where'
            ],
            'company' => [
                'type' => 'in',
                'field' => 'company',
                'filter' => 'like'
            ],
            'tutor_firstname' => [
                'type' => 'in',
                'field' => 'tutor_firstname',
                'filter' => 'where'
            ],
            'tutor_lastname' => [
                'type' => 'in',
                'field' => 'tutor_lastname',
                'filter' => 'where'
            ],
            'tutor_email' => [
                'type' => 'in',
                'field' => 'tutor_email',
                'filter' => 'where'
            ],
            'status' => [
                'type' => 'in',
                'field' => 'status',
                'filter' => 'where',
                'alias' => 'alternance_status'
            ],
            'filiz_folder_id' => [
                'type' => 'in',
                'field' => 'filiz_folder_id',
                'filter' => 'where',
                'alias' => 'filiz_folder_id'
            ],
            'student_firstname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alternance', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' => 'firstname',
                'alias' => 'student_firstname',
                'filter' => 'like'
            ],
            'student_lastname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alternance', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' => 'lastname',
                'alias' => 'student_lastname',
                'filter' => 'like'
            ],
            'student_email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alternance', 'student_fk'], 'right' => ['student', 'id']]
                ],
                'field' => 'email',
                'alias' => 'student_email',
                'filter' => 'where'
            ],
            'student_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alternance', 'student_fk'], 'right' => ['student', 'id']]
                ],
                'field' => 'id',
                'alias' => 'student_id',
                'filter' => 'where'
            ],
            'promotion_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alternance', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' => 'promotion_fk',
                'alias' => 'promotion_id',
                'filter' => 'where'
            ],
            'promotion_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alternance', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'name',
                'alias' => 'promotion_name',
                'filter' => 'where'
            ],
            'promotion_certification' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alternance', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'certification',
                'alias' => 'promotion_certification',
                'filter' => 'where'
            ],
            'promotion_is_active' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alternance', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'is_active',
                'alias' => 'promotion_is_active',
                'filter' => 'where'
            ],
            'current_unit_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alternance', 'student_fk'], 'right' => ['student', 'id']],
                ],
                'alias' => 'current_unit_id',
                'field' => 'current_unit_fk',
                'filter' => 'where',
            ],
        ]);
    }
}
