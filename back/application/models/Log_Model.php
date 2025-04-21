<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Log_Model extends LPTF_Model
{
    private $table = 'log';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getLog($params)
    {
        $constraints = [
            ['id', 'optional', 'number'], ['ip', 'optional', 'number'], 
            ['email', 'optional', 'string'], ['role', 'optional', 'number'],
            ['url', 'optional', 'string'], ['scope', 'optional', 'string'], 
            ['params', 'optional', 'string'], ['method', 'optional', 'string'], 
            ['date', 'optional', 'string'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getLogFields();        
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postLog($params)
{
    $constraints = [
        ['ip', 'optional', 'string'], 
        ['email', 'optional', 'string'], ['role', 'optional', 'number'],
        ['url', 'optional', 'string'],
        ['scope', 'optional', 'number'], ['params', 'optional', 'string'],
        ['method', 'optional', 'string'], ['date', 'optional', 'number'],
    ];

    $this->db->trans_start();
    $data = [
        'ip' => $params['ip'],
        'email' => $params['email'],
        'role' => $params['role'],
        'url' => $params['url'],
        'scope' => isset($params['scope']) ? $params['scope'] : null,
        'params' => $params['params'],
        'method' => $params['method'],
        'date' => $params['date'],
    ];

    $response = $this->db->insert($this->table, $data);
    $this->db->trans_complete();
}

    public function putLog($params)
    {
        $constraints = [
            ['ip', 'optional', 'string'], ['id', 'mandatory', 'number'], 
            ['email', 'optional', 'string'], ['role', 'optional', 'number'],
            ['url', 'optional', 'string'],
            ['scope', 'optional', 'string'], ['params', 'optional', 'string'],
            ['method', 'optional', 'string'], ['date', 'optional', 'string'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }
        $data = [];

        if (count($data) > 0) {
            $this->db->where('id', $params['id']);
            $response = $this->db->update($this->table, $data);
        } else {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteLog($params)
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

    private function getLogFields()
    {
        return ([
            'ip' => [
                'type' => 'in', 
                'field' => 'ip', 
                'filter' => 'where'
            ],
            'id' => [
                'type' => 'in', 
                'field' => 'id',
                'filter' => 'where'
            ],
            'email' => [
                'type' => 'in', 
                'field' => 'email',
                'filter' => 'where', 
            ],
            'role' => [
                'type' => 'in', 
                'field' => 'role',
                'filter' => 'where', 
            ],
            'url' => [
                'type' => 'in', 
                'field' => 'url',
                'filter' => 'where', 
            ],
            'scope' => [
                'type' => 'in',
                'field' => 'scope',
                'filter' => 'where'
            ],
            'params' => [
                'type' => 'in', 
                'field' => 'params',
                'filter' => 'where', 
            ],
            'method' => [
                'type' => 'in', 
                'field' => 'method',
                'filter' => 'where', 
            ],
            'date' => [
                'type' => 'in', 
                'field' => 'date',
                'filter' => 'where', 
            ],
        ]);
    }
}

?>