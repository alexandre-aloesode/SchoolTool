<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Class_Model extends LPTF_Model
{
    private $table = 'class';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getClass($params)
    {
        $constraints = [
            ['class_id', 'optional', 'number', true], ['class_name', 'optional', 'string', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getClassFields();    
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postClass($params)
    {
        $constraints = [
            ['class_name', 'mandatory', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [
            'name' => $params['class_name']
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putClass($params)
    {
        $constraints = [
            ['class_id', 'mandatory', 'number'], ['class_name', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [];
        if (array_key_exists('class_name', $params))
        {
            $data['name'] = $params['class_name'];
        }

        if (count($data) > 0)
        {
            $this->db->where('id', $params['class_id']);
            $response = $this->db->update($this->table, $data);
        }
        else
        {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteClass($params)
    {
        $constraints = [
            ['class_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->where_in('id', $params['class_id']);
        $response = $this->db->delete($this->table);
        
        return ($response);
    }

    private function getClassFields()
    {
        return ([
			'class_id' => [
                'type' => 'in',
                'field' =>'id',
                'filter' => 'where'
            ],
			'class_name' => [
                'type' => 'in',
                'field' => 'name',
                'filter' => 'like'
            ]
        ]);
    }
}

?>