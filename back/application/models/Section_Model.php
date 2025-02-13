<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Section_Model extends LPTF_Model
{
    private $table = 'section';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getSection($params)
    {
        $constraints = [
            ['section_id', 'optional', 'number', true], ['section_name', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->GetSectionFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postSection($params)
    {
        $constraints = [
            ['section_name', 'mandatory', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [
            'name' => $params['section_name']
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putSection($params)
    {
        $constraints = [
            ['section_id', 'mandatory', 'number'], ['section_name', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [];
        if (array_key_exists('section_name', $params))
        {
            $data['name'] = $params['section_name'];
        }

        if (count($data) > 0)
        {
            $this->db->where('id', $params['section_id']);
            $response = $this->db->update($this->table, $data);
        }
        else
        {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteSection($params)
    {
        $constraints = [
            ['section_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->where_in('id', $params['section_id']);
        $response = $this->db->delete($this->table);
        
        return ($response);
    }

    private function GetSectionFields()
    {
        return ([
            'section_id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'section_name' => [
                'type' => 'in',
                'field' => 'name',
                'filter' => 'like'
            ]
        ]);
    }
}

?>