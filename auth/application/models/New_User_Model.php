<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'core/LPTF_Model.php';

class New_User_Model extends LPTF_Model
{
    private $table = 'user';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getUser($params)
    {
        $scope = false;

        $constraints = [
            ['id', 'optional', 'number'], ['email', 'optional', 'string'], ['role_id', 'optional', 'number'],
            ['role_name', 'optional', 'string'], ['scope', 'optional', 'number']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        if (isset($params['scope'])) {
            $scope = $params['scope'];
            unset($params['scope']);
        }

        $fields = $this->getUserFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        $users_arr = $query->result_array();
        $newarr = [];

        if ($scope !== false) {
            $this->load->model('Scope_Model');
            foreach ($users_arr as $key => $user) {
                $params = [
                    'id' => '',
                    'user_id' => $user['user_id'],
                    'scope_value' => '',
                ];
                $user_scopes = $this->Scope_Model->getScope($params);
                if ($scope == "") {
                    $users_arr[$key]['scope'] = $user_scopes;
                } else {
                    foreach ($user_scopes as $user_scope) {
                        if ($user_scope['scope_scope_value'] == $scope) {
                            $users_arr[$key]['scope'] = $user_scopes;
                        }
                    }
                }
                if (isset($users_arr[$key]['scope'])) {
                    $newarr[] = $users_arr[$key];
                }
            }
        }

        return ($scope !== false ? $newarr : $users_arr);
    }

    public function postUser($params)
    {
        $constraints = [
            ['email', 'mandatory', 'string'], ['role_id', 'mandatory', 'number'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [
            'email' => $params['email'],
            'role_id' => $params['role_id'],
        ];

        $response = $this->db->insert($this->table, $data);

        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putUser($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number'], ['email', 'optional', 'string'],
            ['role_id', 'optional', 'number'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['email', 'email'],
            ['role_id', 'role_id'],
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = null;
                if (strlen($params[$field[1]]) > 0) {
                    $data[$field[0]] = $params[$field[1]];
                }
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

    public function deleteUser($params)
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

    public function postNewStudents($params)
    {
        $constraints = [
            ['email', 'mandatory', 'string'], ['role_id', 'mandatory', 'number'],
        ];

        $student_id = $params['student_id'];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [
            'email' => $params['email'],
            'role_id' => $params['role_id'],
        ];

        $response = $this->db->insert($this->table, $data);

        if($response === true){
            $this->load->model('Scope_Model');
            $data = [
                'user_id' => (string)$this->db->insert_id(),
                'scope_value' => (string)$student_id,
            ];
            $scopeResponse = $this->Scope_Model->postScope($data);
        }

        return ($response === true ? $this->db->insert_id() : false);
    }


    private function getUserFields()
    {
        return ([
            'id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'email' => [
                'type' => 'in',
                'field' => 'email',
                'filter' => 'like'
            ],
            'role_id' => [
                'type' => 'in',
                'field' => 'role_id',
                'filter' => 'where'
            ],
            'role_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['user', 'role_id'], 'right' => ['role', 'id']]
                ],
                'alias' => 'role_name',
                'field' => 'name',
                'filter' => 'where',
            ],
            'scope' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['user', 'id'], 'right' => ['scope', 'user_id']]
                ],
                'alias' => 'scope',
                'field' => 'scope_value',
                'filter' => 'where',
            ],
        ]);
    }
}
