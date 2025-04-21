<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/LPTF_Model.php';

class Role_Model extends LPTF_Model
{
    private $table = 'role';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getRole($params)
    {
        $constraints = [
            ['id', 'optional', 'number'], ['name', 'optional', 'string'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getRoleFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        $roles_arr = $query->result_array();

        return ($roles_arr);
    }

    public function postRole($params)
    {
        $constraints = [
            ['name', 'mandatory', 'string'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [
            'name' => $params['name'],
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putRole($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number'], ['name', 'optional', 'string'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['name', 'name'],
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

    public function deleteRole($params)
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


    private function getRoleFields()
    {
        return ([
            'id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'name' => [
                'type' => 'in',
                'field' => 'name',
                'filter' => 'where'
            ],      
        ]);
    }
}
