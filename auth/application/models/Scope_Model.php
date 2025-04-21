<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/LPTF_Model.php';

class Scope_Model extends LPTF_Model
{
    private $table = 'scope';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getScope($params)
    {
        $constraints = [
            ['id', 'optional', 'number'], ['user_id', 'optional', 'number'],
            ['scope_value', 'optional', 'number'],
            ['user_email', 'optional', 'string'], ['user_role', 'optional', 'string', true],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getScopeFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        $scopes_arr = $query->result_array();
        return ($scopes_arr);
    }

    public function postScope($params)
    {
        $constraints = [
            ['user_id', 'mandatory', 'number'], ['scope_value', 'mandatory', 'number'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [
            'user_id' => $params['user_id'],
            'scope_value' => $params['scope_value'],
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putScope($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number'], ['user_id', 'optional', 'number'],
            ['scope_value', 'optional', 'number'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['user_id', 'user_id'],
            ['scope_value', 'scope_value'],
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

    public function deleteScope($params)
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


    private function getScopeFields()
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
            'scope_value' => [
                'type' => 'in',
                'field' => 'scope_value',
                'filter' => 'where'
            ],
            'user_email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['scope', 'user_id'], 'right' => ['user', 'id']],
                ],
                'alias' => 'user_email',
                'field' => 'email',
                'filter' => 'where',
            ],
            'user_role' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['scope', 'user_id'], 'right' => ['user', 'id']],
                    ['left' => ['user', 'role_id'], 'right' => ['role', 'id']],
                ],
                'alias' => 'user_role',
                'field' => 'name',
                'filter' => 'where',
            ],
        ]);
    }
}
