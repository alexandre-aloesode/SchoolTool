<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar_Model extends LPTF_Model
{
    private $table = 'calendar';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getCalendar($params)
    {
        $constraints = [
            ['id', 'optional', 'number', true], ['promotion_id', 'optional', 'number', true], 
            ['name', 'optional', 'string', true], ['status', 'optional', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getCalendarFields();    
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postCalendar($params)
    {
        $constraints = [
            ['promotion_id', 'mandatory', 'number'], 
            ['name', 'mandatory', 'string'], ['status', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [
            'promotion_fk' => $params['promotion_id'],
            'name' => $params['name'],
            'status' => $params['status'],
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putCalendar($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number'], ['promotion_id', 'optional', 'number'], 
            ['name', 'optional', 'string'], ['status', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [];
        if (array_key_exists('name', $params))
        {
            $data['name'] = $params['name'];
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

    public function deleteCalendar($params)
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

    private function getCalendarFields()
    {
        return ([
			'id' => [
                'type' => 'in',
                'field' =>'id',
                'filter' => 'where'
            ],
			'promotion_id' => [
                'type' => 'in',
                'field' => 'promotion_fk',
                'filter' => 'where',
                'alias' => 'promotion_id'
            ]
            ,
			'name' => [
                'type' => 'in',
                'field' => 'name',
                'filter' => 'like'
            ],
			'status' => [
                'type' => 'in',
                'field' => 'status',
                'filter' => 'where'
            ]
        ]);
    }
}

?>