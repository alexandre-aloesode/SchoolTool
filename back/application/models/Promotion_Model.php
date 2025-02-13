<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Promotion_Model extends LPTF_Model
{
    private $table = 'promotion';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getPromotion($params)
    {
        $constraints = [
            ['promotion_id', 'optional', 'number', true], ['promotion_name', 'optional', 'string'],
            ['is_active', 'optional', 'boolean'], ['start_date', 'optional', 'string'],
            ['end_date', 'optional', 'string'], ['promotion_year', 'optional', 'string'],
            ['promotion_duration', 'optional', 'number'], ['certification', 'optional', 'string'],
            ['formation_type', 'optional', 'string'],
            ['calendar_id', 'optional', 'number'], ['section_id', 'optional', 'number'], 
            ['section_name', 'optional', 'string'], ['calendar_days', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getPromotionFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postPromotion($params)
    {
        $constraints = [
            ['promotion_name', 'mandatory', 'string'], ['promotion_year', 'mandatory', 'string'],
            ['section_id', 'mandatory', 'number'], ['is_active', 'optional', 'boolean'],['formation_type', 'optional', 'string'],
            ['promotion_start_date', 'optional', 'string'], ['promotion_end_date', 'optional', 'string'],
            ['promotion_duration', 'optional', 'number'], ['certification', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [
            'name' => $params['promotion_name'],
            'year' => $params['promotion_year'],
            'section_fk' => $params['section_id']
        ];

        if (array_key_exists('is_active', $params)) {
            $data['is_active'] = $params['is_active'];
        }

        $response = $this->db->insert($this->table, $data);

        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putPromotion($params)
    {
        $constraints = [
            ['promotion_id', 'mandatory', 'number'], ['promotion_name', 'optional', 'string'], ['is_active', 'optional', 'boolean'], 
            ['start_date', 'optional', 'string'], ['end_date', 'optional', 'string'], ['duration', 'optional', 'number'],
            ['promotion_year', 'optional', 'string'], ['certification', 'optional', 'string'], ['section_id', 'optional', 'number'],
            ['formation_type', 'optional', 'string']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['name', 'promotion_name'], ['is_active', 'is_active'], ['formation_type', 'formation_type'], 
            ['start_date', 'start_date'], ['end_date', 'end_date'], ['duration', 'duration'],
            ['year', 'promotion_year'], ['certification', 'certification'], ['section_fk', 'section_id']
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        if (count($data) > 0) {
            $this->db->where('id', $params['promotion_id']);
            $response = $this->db->update($this->table, $data);
        } else {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function putPromotionToEnd($params)
    {
        $constraints = [
            ['promotion_id', 'mandatory', 'number', true]

        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->trans_start();

        $this->load->model('Student_Model');
        $student_params = [
            'student_id' => '',
            'promotion_id' => $params['promotion_id']
        ];
        $promotion_students = $this->Student_Model->getStudent($student_params);

        foreach ($promotion_students as $student) {
            $sql_unit_viewer = "DELETE uw FROM unit_viewer as uw
            WHERE student_fk = ?";
            $response_unit_viewer = $this->db->query($sql_unit_viewer, [$student['student_id']]);

            $sql_reg = "DELETE reg FROM registration as reg
            WHERE member_fk = ? AND is_complete = 0";
            $response_reg = $this->db->query($sql_reg, [$student['student_id']]);

            $sql_wl = "DELETE wl FROM waiting_list as wl
                    WHERE student_fk = ?";
            $response_wl = $this->db->query($sql_wl, [$student['student_id']]);
        }

        $promo_params = [
            'promotion_id' => $params['promotion_id'],
            'is_active' => '0',
        ];
        $this->putPromotion($promo_params);

        $this->db->trans_complete();

        return (true);
    }

    public function deletePromotion($params)
    {
        $constraints = [
            ['promotion_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->where_in('id', $params['promotion_id']);
        $response = $this->db->delete($this->table);

        return ($response);
    }

    public function getPromotionFaithfulness($params)
    {
        $constraints = [
            ['promotion_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->load->model('Student_Model');
        $this->load->model('Promotion_History_Model');

        $allApplicants = $this->Promotion_History_Model->getPromotionHistory([
            'promotion_id' => $params['promotion_id'],
            'applicant_id' => '',
        ]);

        $faithfulness_array = [];

        foreach ($allApplicants as $applicant) {
            array_push($faithfulness_array, $this->Student_Model->getStudentFaithfulness(['applicant_id' => $applicant['applicant_id'], 'promotion_id' => $params['promotion_id']]));
        }

        return ($faithfulness_array);
    }

    private function getPromotionFields()
    {
        return ([
            'promotion_id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'promotion_name' => [
                'type' => 'in',
                'field' => 'name',
                'filter' => 'like'
            ],
            'is_active' => [
                'type' => 'in',
                'field' => 'is_active',
                'filter' => 'where'
            ],
            'formation_type' => [
                'type' => 'in', 
                'field' => 'formation_type', 
                'alias' => 'formation_type',
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
            'promotion_duration' => [
                'type' => 'in',
                'field' => 'duration',
                'filter' => 'where'
            ],
            'promotion_year' => [
                'type' => 'in',
                'field' => 'year',
                'alias' => 'promotion_year',
                'filter' => 'where'
            ],
            'certification' => [
                'type' => 'in',
                'field' => 'certification',
                'filter' => 'where'
            ],
            'section_id' => [
                'type' => 'in',
                'field' => 'section_fk',
                'filter' => 'where'
            ],
            'section_name' => [
                'type' => 'out',
                'field' => 'name',
                'alias' => 'section_name',
                'link' => [
                    ['left' => ['promotion', 'section_fk'], 'right' => ['section', 'id']]
                ],
                'filter' => 'where'
            ],
            'calendar_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['promotion', 'id'], 'right' => ['calendar', 'promotion_fk']]
                ],
                'alias' => 'calendar_id',
                'field' => 'id',
                'filter' => 'where',
            ],
        ]);
    }
}
