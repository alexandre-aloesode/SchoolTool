<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Followup_Model extends LPTF_Model
{
    private $table = 'followup';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getFollowup($params)
    {
        $constraints = [
            ['followup_id', 'optional', 'number', true], ['creation_date', 'optional', 'string'],
            ['author', 'optional', 'string'], ['comment', 'optional', 'string'],
            ['applicant_id', 'optional', 'number', true], ['student_id', 'optional', 'number'],
            ['student_firstname', 'optional', 'string'], ['student_lastname', 'optional', 'string'],
            ['email', 'optional', 'string'], ['type', 'optional', 'string'], ['current_unit_id', 'optional', 'number', true],
            ['promotion_id', 'optional', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getFollowupFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postFollowup($params)
    {
        $constraints = [
            ['applicant_id', 'mandatory', 'number'], ['comment', 'mandatory', 'string'],
            ['type', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->trans_start();

        $email = $this->token_helper->get_payload()['user_email'];
        if ($email === null) return ($this->Status()->ExpectationFailed());

        $data = [
            'applicant_fk' => $params['applicant_id'],
            'comment' => $params['comment'],
            'creation_date' => date('Y-m-d H:i:s'),
            'author' => $email,
            'type' => isset($params['type']) ? $params['type'] : 'PEDA'
        ];

        $optional_fields = [
            ['type', 'type'],
        ];

        foreach ($optional_fields as $field)
        {
            if (array_key_exists($field[1], $params))
            {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        $response = $this->db->insert($this->table, $data);
        $this->db->trans_complete();
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function deleteFollowup($params)
    {
        $constraints = [
            ['followup_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->trans_start();
        $this->db->where_in('id', $params['followup_id']);
        $response = $this->db->delete($this->table);
        $this->db->trans_complete();
        return ($response);
    }

    public function putFollowup($params)
    {
        $constraints = [
            ['followup_id', 'mandatory', 'number'], ['applicant_id', 'optional', 'number'],
            ['comment', 'optional', 'string'], ['type', 'optional', 'string'],
            ['author', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->trans_start();
        $data = [];
        $optional_fields = [
            ['applicant_id', 'applicant_id'],
            ['comment', 'comment'],
            ['type', 'type'],
            ['author', 'author']
        ];
    
        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        $this->db->where('id', $params['followup_id']);
        $response = $this->db->update($this->table, $data);
        $this->db->trans_complete();
        return ($response);
    }

    public function getStudentFollowup($params)
    {
        return ($this->getFollowup($params));
    }

    public function postStudentFollowup($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['comment', 'mandatory', 'string'],
            ['type', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->trans_start();
        $this->db->select('applicant_fk');
        $this->db->where('id', $params['student_id']);
        $response = $this->db->get("student");
        $regs = $response->result_array();

        if (count($regs) != 1)
        {
            return ($this->Status()->Forbidden());
        }

        $fields = [
            'applicant_id' => $regs[0]['applicant_fk'],
            'comment' => $params['comment'],
            'type' => $params['type']
        ];
        // var_dump($fields);

        $response = $this->PostFollowup($fields);
        $this->db->trans_complete();

        return ($response);
    }

    private function getFollowupFields()
    {
        return ([
			'followup_id' => [
                'type' => 'in',
                'field' =>'id',
                'filter' => 'where'
            ],
            'creation_date' => [
                'type' => 'in',
                'field' => 'creation_date',
                'alias' => 'followup_creation_date',
                'filter' => 'none'
            ],
            'author' => [
                'type' => 'in',
                'field' =>'author',
                'filter' => 'like'
            ],
            'comment' => [
                'type' => 'in',
                'field' =>'comment',
                'filter' => 'none'
            ],
            'applicant_id' => [
                'type' => 'in',
                'field' =>'applicant_fk',
                'alias' => 'followup_applicant_id',
                'filter' => 'where'
            ],
            'student_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['followup', 'applicant_fk'], 'right' => ['student', 'applicant_fk']],                 
                ],
                'field' => 'id',
                'alias' => 'followup_student_id',
                'filter' => 'where'
            ],
            'student_lastname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['followup', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' =>'lastname',
                'alias' => 'student_lastname',
                'filter' => 'like'
            ],
            'student_firstname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['followup', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' =>'firstname',
                'alias' => 'student_firstname',
                'filter' => 'like'
            ],
			'email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['followup', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' =>'email',
                'alias' => 'applicant_email',
                'filter' => 'like'
            ],
            'type' => [
                'type' => 'in',
                'field' => 'type',
                'filter' => 'where'
            ],
            'current_unit_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['followup', 'applicant_fk'], 'right' => ['student', 'applicant_fk']],
                ],
                'alias' => 'current_unit_id',
                'field' => 'current_unit_fk',
                'filter' => 'where',
            ],
            'promotion_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['followup', 'applicant_fk'], 'right' => ['applicant', 'id']],
                ],
                'alias' => 'promotion_id',
                'field' => 'promotion_fk',
                'filter' => 'where',
            ],
        ]);
    }
}

?>