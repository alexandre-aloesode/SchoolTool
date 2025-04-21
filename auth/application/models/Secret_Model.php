<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/LPTF_Model.php';

class Secret_Model extends LPTF_Model
{
    private $table = 'secret';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getSecret($params)
    {
        $constraints = [
            ['id', 'optional', 'number'], ['user_id', 'optional', 'number'], ['secret', 'optional', 'string'],
            ['issue_date', 'optional', 'string'], ['status', 'optional', 'number'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getSecretFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        $secrets_arr = $query->result_array();

        return ($secrets_arr);
    }

    public function postSecret($params)
    {
        $constraints = [
            ['user_id', 'optional', 'number'], ['secret', 'optional', 'string'],
            ['issue_date', 'optional', 'string'], ['status', 'optional', 'number'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [
            'user_id' => $params['user_id'],
            'secret' => $params['secret'],
            'issue_date' => $params['issue_date'],
            'status' => $params['status'],
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putSecret($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number'], ['user_id', 'optional', 'number'], ['secret', 'optional', 'string'],
            ['issue_date', 'optional', 'string'], ['status', 'optional', 'number'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['user_id', 'user_id'],
            ['secret', 'secret'],
            ['issue_date', 'issue_date'],
            ['status', 'status'],
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = null;
                if (strlen($params[$field[1]]) > 0) {
                    $data[$field[0]] = $params[$field[1]];
                }
            }
        }
 
        if (count($data) > 0)
        {
            $this->db->where('id', $params['id']);
            $response = $this->db->update($this->table, $data);
        }
        else
        {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteSecret($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->where_in('id', $params['id']);
        $response = $this->db->delete($this->table);

        return ($response);
    }


    private function getSecretFields()
    {
        return ([
            'id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'user_id' => [
                'type' => 'in',
                'field' => 'user_id',
                'filter' => 'where'
            ],
            'secret' => [
                'type' => 'in',
                'field' => 'secret',
                'filter' => 'where'
            ],
            'issue_date' => [
                'type' => 'in',
                'field' => 'issue_date',
                'filter' => 'where'
            ],
            'status' => [
                'type' => 'in',
                'field' => 'status',
                'filter' => 'where'
            ],     
        ]);
    }
}
