<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logtime_Event_Model extends LPTF_Model
{
    private $table = 'logtime_event';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getLogtime_Event($params)
    {
        $constraints = [
            ['id', 'optional', 'number'], ['student_id', 'optional', 'number', true], ['username', 'optional', 'string', true],
            ['adm_email', 'optional', 'number'], ['creation_date', 'optional', 'number'], 
            ['duration', 'optional', 'string'], ['logtime_date', 'optional', 'string'],
            ['reason', 'optional', 'string'], ['date_before', 'optional', 'string'],['date_after', 'optional', 'string'], 
            ['promotion_id', 'optional', 'number', true], ['promotion_name', 'optional', 'string'], ['promotion_is_active', 'optional', 'number']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getLogtime_EventFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        $logtimes_arr = $query->result_array();
        return ($logtimes_arr);
    }

    public function postLogtime_Event($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'],
            ['duration', 'mandatory', 'string'], ['logtime_date', 'mandatory', 'string'],
            ['reason', 'mandatory', 'string']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }
        $adm_email = $this->token_helper->get_payload()['user_email'];
        if ($adm_email === null) return ($this->Status()->ExpectationFailed());

        $todayDate = new DateTime();
        $data = [
            'student_fk' => $params['student_id'],
            'adm_email' => $adm_email == null ? '' : $adm_email,
            'creation_date' => $todayDate->format('Y-m-d'),
            'duration' => $params['duration'],
            'logtime_date' => $params['logtime_date'],
            'reason' => $params['reason'],
        ];

        $response = $this->db->insert($this->table, $data);

        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putLogtime_Event($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number'], ['student_id', 'optional', 'number'],
            ['duration', 'optional', 'string'], ['logtime_date', 'optional', 'string'],
            ['reason', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [];
        $optional_fields = [
            ['duration', 'duration'],
            ['logtime_date', 'logtime_date'],
            ['reason', 'reason'],
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

    public function deleteLogtime_Event($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->where_in('id', $params['id']);
        $response = $this->db->delete($this->table);
        
        return ($response);
    }

    private function getLogtime_EventFields()
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
            'adm_email' => [
                'type' => 'in',
                'field' => 'adm_email',
                'filter' => 'where'
            ],
            'creation_date' => [
                'type' => 'in',
                'field' => 'creation_date',
                'filter' => 'where'
            ],
            'duration' => [
                'type' => 'in',
                'field' => 'duration',
                'filter' => 'where'
            ],
            'logtime_date' => [
                'type' => 'in',
                'field' => 'logtime_date',
                'filter' => 'where'
            ],
            'reason' => [
                'type' => 'in',
                'field' => 'reason',
                'filter' => 'where'
            ],
            'date_after' => [
                'type' => 'filter',
                'field' => 'logtime_date >=',
                'filter' => 'where'
            ],
            'date_before' => [
                'type' => 'filter',
                'field' => 'logtime_date <=',
                'filter' => 'where'
            ],
            'promotion_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['logtime_event', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']],
                ],
                'alias' => 'promotion_id',
                'field' => 'id',
                'filter' => 'where',
            ],
            'promotion_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['logtime_event', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']],
                ],
                'alias' => 'promotion_name',
                'field' => 'name',
                'filter' => 'where',
            ],
            'promotion_is_active' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['logtime_event', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']],
                ],
                'alias' => 'promotion_is_active',
                'field' => 'is_active',
                'filter' => 'where',
            ],
            'username' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['logtime_event', 'student_fk'], 'right' => ['student', 'id']],
                ],
                'alias' => 'username',
                'field' => 'email',
                'filter' => 'where',
            ],
        ]);
    }
}
