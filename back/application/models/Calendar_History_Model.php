<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar_History_Model extends LPTF_Model
{
    private $table = 'calendar_history';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getCalendarHistory($params)
    {
        $constraints = [
            ['id', 'optional', 'number', true], ['applicant_id', 'optional', 'number', true], ['student_id', 'optional', 'number', true],
            ['calendar_id', 'optional', 'number', true], 
            ['date', 'optional', 'string', true], ['author', 'optional', 'string', true],
            ['name', 'optional', 'string', true], 
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getCalendarHistoryFields();    
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postCalendarHistory($params)
    {
        $constraints = [
            ['applicant_id', 'mandatory', 'number'], ['calendar_id', 'mandatory', 'number'],
            ['author', 'mandatory', 'string'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }
        $date = date('Y-m-d');
        $data = [
            'applicant_id' => $params['applicant_id'],
            'calendar_id' => $params['calendar_id'],
            'date' => $date,
            'author' => $params['author']
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putCalendarHistory($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number'], ['applicant_id', 'optional', 'number'], ['calendar_id', 'optional', 'number'],
            ['author', 'optional', 'string'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [];
        $optional_fields = [
            ['applicant_id', 'applicant_id'],
            ['calendar_id', 'calendar_id'], 
            ['author', 'author'],
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

    public function deleteCalendarHistory($params)
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

    private function getCalendarHistoryFields()
    {
        return ([
			'id' => [
                'type' => 'in',
                'field' =>'id',
                'filter' => 'where'
            ],
			'applicant_id' => [
                'type' => 'in',
                'field' => 'applicant_fk',
                'filter' => 'where'
            ],
            'student_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['calendar_history', 'applicant_fk'], 'right' => ['student', 'applicant_fk']]
                ],
                'alias' => 'student_id',
                'field' =>'id',
                'filter' => 'where',
            ],
            'calendar_id' => [
                'type' => 'in',
                'field' => 'calendar_fk',
                'filter' => 'where'
            ],
			'date' => [
                'type' => 'in',
                'field' => 'date',
                'alias' => 'date',
                'filter' => 'where'
            ],
			'author' => [
                'type' => 'in',
                'field' => 'author',
                'filter' => 'where'
            ],
            'name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['calendar_history', 'calendar_fk'], 'right' => ['calendar', 'id']],
                ],
                'alias' => 'name',
                'field' => 'name',
                'filter' => 'where',
            ],
            'is_active' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['calendar_history', 'calendar_fk'], 'right' => ['calendar', 'id']],
                ],
                'alias' => 'is_active',
                'field' => 'status',
                'filter' => 'where',
            ],
        ]);
    }
}

?>