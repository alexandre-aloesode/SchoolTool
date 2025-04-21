<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unit_History_Model extends LPTF_Model
{
    private $table = 'unit_history';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getUnitHistory($params)
    {
        $constraints = [
            ['unit_history_id', 'optional', 'number'], ['student_id', 'optional', 'number'],
            ['unit_id', 'optional', 'number'], ['unit_name', 'optional', 'string'], ['unit_code', 'optional', 'string'],
            ['date', 'optional', 'string'], ['author', 'optional', 'string'],
            ['date_before', 'optional', 'string'], ['date_after', 'optional', 'string'],
            ['firstname', 'optional', 'string'], ['lastname', 'optional', 'string'],
            ['student_id', 'optional', 'number'], ['current_unit_name', 'optional', 'string'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getUnitHistoryFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postUnitHistory($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['unit_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [
            'student_fk' => $params['student_id'],
            'unit_fk' => $params['unit_id'],
            'author' => $this->token_helper->get_payload()['user_email'],
            'date' => date('Y-m-d')
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putUnitHistory($params)
    {
        $constraints = [
            ['unit_history_id', 'mandatory', 'number'], ['student_id', 'optional', 'number'],
            ['unit_id', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [
            'author' => $this->token_helper->get_payload()['user_email'],
        ];
        $optional_fields = [
            ['student_fk', 'student_id'],
            ['unit_fk', 'unit_id'],
        ];

        foreach ($optional_fields as $field) 
        {
            if (array_key_exists($field[1], $params)) 
            {
                if (strlen($params[$field[1]]) == 0) 
                {
                    $data[$field[0]] = null;
                } 
                else 
                {
                    $data[$field[0]] = $params[$field[1]];
                }
            }
        }

        if (count($data) > 0)
        {
            $this->db->where('id', $params['unit_history_id']);
            $response = $this->db->update($this->table, $data);
        }
        else
        {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteUnitHistory($params)
    {
        $constraints = [
            ['unit_history_id', 'mandatory', 'number', true]
        ];
        
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->where_in('id', $params['unit_history_id']);
        $response = $this->db->delete($this->table);
        
        return ($response);
    }

    private function getUnitHistoryFields()
    {
        return ([
			'unit_history_id' => [
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
            'unit_id' => [
                'type' => 'in',
                'field' => 'unit_fk',
                'alias' => 'unit_id',
                'filter' => 'where'
            ],
            'unit_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_history', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'alias' => 'unit_name',
                'field' =>'name',
                'filter' => 'like',
            ],
            'unit_code' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_history', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'alias' => 'unit_code',
                'field' =>'code',
                'filter' => 'like',
            ],
            'date' => [
                'type' => 'in',
                'field' => 'date',
                'filter' => 'where'
            ],
            'date_after' => [
                'type' => 'filter',
                'field' => 'date >=',
                'filter' => 'where'
            ],
            'date_before' => [
                'type' => 'filter',
                'field' => 'date <=',
                'filter' => 'where'
            ],
            'author' => [
                'type' => 'in',
                'field' => 'author',
                'filter' => 'where'
            ],
            'firstname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_history', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'alias' => 'student_firstname',
                'field' =>'firstname',
                'filter' => 'where',
            ],
            'lastname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_history', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'alias' => 'student_lastname',
                'field' =>'lastname',
                'filter' => 'where',
            ],
            'current_unit_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['unit_history', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'current_unit_fk'], 'right' => ['unit', 'id']]
                ],
                'alias' => 'current_unit_name',
                'field' =>'name',
                'filter' => 'where',
            ],
            
        ]);
    }
}

?>