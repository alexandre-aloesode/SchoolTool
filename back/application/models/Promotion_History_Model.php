<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promotion_History_Model extends LPTF_Model
{
    private $table = 'promotion_history';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getPromotionHistory($params)
    {
        $constraints = [
            ['promotion_history_id', 'optional', 'number'], ['applicant_id', 'optional', 'number'],
            ['promotion_id', 'optional', 'number', true], ['promotion_name', 'optional', 'string'],
            ['date', 'optional', 'string'], ['author', 'optional', 'string'],
            ['date_before', 'optional', 'string'], ['date_after', 'optional', 'string'],
            ['firstname', 'optional', 'string'], ['lastname', 'optional', 'string'],
            ['student_id', 'optional', 'number', true], ['current_unit_name', 'optional', 'string'],
            ['promotion_is_active', 'optional', 'boolean']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getPromotionHistoryFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postPromotionHistory($params)
    {
        $constraints = [
            ['applicant_id', 'mandatory', 'number'], ['promotion_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [
            'applicant_fk' => $params['applicant_id'],
            'promotion_fk' => $params['promotion_id'],
            'author' => $this->token_helper->get_payload()['user_email'],
            'date' => date('Y-m-d')
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putPromotionHistory($params)
    {
        $constraints = [
            ['promotion_history_id', 'mandatory', 'number'], ['applicant_id', 'optional', 'number'],
            ['promotion_id', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [
            'author' => $this->token_helper->get_payload()['user_email'],
        ];
        $optional_fields = [
            ['applicant_fk', 'applicant_id'],
            ['promotion_fk', 'promotion_id'],
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
            $this->db->where('id', $params['promotion_history_id']);
            $response = $this->db->update($this->table, $data);
        }
        else
        {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deletePromotionHistory($params)
    {
        $constraints = [
            ['promotion_history_id', 'mandatory', 'number', true]
        ];
        
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->where_in('id', $params['promotion_history_id']);
        $response = $this->db->delete($this->table);
        
        return ($response);
    }

    private function getPromotionHistoryFields()
    {
        return ([
			'promotion_history_id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'applicant_id' => [
                'type' => 'in',
                'field' => 'applicant_fk',
                'alias' => 'applicant_id',
                'filter' => 'where'
            ],
            'promotion_id' => [
                'type' => 'in',
                'field' => 'promotion_fk',
                'alias' => 'promotion_id',
                'filter' => 'where'
            ],
            'promotion_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['promotion_history', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'alias' => 'promotion_name',
                'field' =>'name',
                'filter' => 'like',
            ],
            'promotion_is_active' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['promotion_history', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'alias' => 'promotion_is_active',
                'field' =>'is_active',
                'filter' => 'where',
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
                    ['left' => ['promotion_history', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'alias' => 'student_firstname',
                'field' =>'firstname',
                'filter' => 'where',
            ],
            'lastname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['promotion_history', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'alias' => 'student_lastname',
                'field' =>'lastname',
                'filter' => 'where',
            ],
            'student_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['promotion_history', 'applicant_fk'], 'right' => ['student', 'applicant_fk']]
                ],
                'alias' => 'student_id',
                'field' =>'id',
                'filter' => 'where',
            ],
            'current_unit_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['promotion_history', 'applicant_fk'], 'right' => ['student', 'applicant_fk']],
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