<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skill_Model extends LPTF_Model
{
    private $table = 'skill';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getSkill($params)
    {
        $constraints = [
            ['skill_id', 'optional', 'number'], ['skill_name', 'optional', 'string'],
            ['skill_code', 'optional', 'string'], ['class_id', 'optional', 'number'],
            ['class_name', 'optional', 'string', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getSkillFields();        
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postSkill($params)
    {
        $constraints = [
            ['skill_name', 'mandatory', 'string'], ['skill_code', 'mandatory', 'string'],
            ['class_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $params['skill_name'] = strtolower($params['skill_name']);
        $params['skill_name'][0] = strtoupper($params['skill_name'][0]);

        $data = [
            'name' => $params['skill_name'],
            'code' => $params['skill_code'],
            'class_fk' => $params['class_id']
        ];

        $response = $this->db->insert($this->table, $data);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putSkill($params)
    {
        $constraints = [
            ['skill_id', 'mandatory', 'number'], ['skill_name', 'optional', 'string'],
            ['skill_code', 'optional', 'string'], ['class_id', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        if(isset($params['skill_name'])){
            $params['skill_name'] = strtolower($params['skill_name']);
            $params['skill_name'][0] = strtoupper($params['skill_name'][0]);
        }

        $data = [];
        $optional_fields = [
            ['name', 'skill_name'],
            ['code', 'skill_code'],
            ['class_fk', 'class_id']
        ];
    
        foreach ($optional_fields as $field)
        {
            if (array_key_exists($field[1], $params))
            {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        if (count($data) > 0)
        {
            $this->db->where('id', $params['skill_id']);
            $response = $this->db->update($this->table, $data);
        }
        else
        {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteSkill($params)
    {
        $constraints = [
            ['skill_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->where_in('id', $params['skill_id']);
        $response = $this->db->delete($this->table);
        
        return ($response);
    }

    private function getSkillFields()
    {
        return ([
            'skill_id' => [
                'type' => 'in', 
                'field' => 'id', 
                'filter' => 'where'
            ],
            'skill_name' => [
                'type' => 'in', 
                'field' => 'name',
                'alias' => 'skill_name',
                'filter' => 'like'
            ],
            'skill_code' => [
                'type' => 'in', 
                'field' => 'code',
                'alias' => 'skill_code',
                'filter' => 'like', 
            ],
            'class_id' => [
                'type' => 'in', 
                'field' => 'class_fk',
                'alias' => 'class_id',
                'filter' => 'where', 
            ],
            'class_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['skill', 'class_fk'], 'right' => ['class', 'id']]
                ],
                'field' => 'name',
                'filter' => 'like'
            ]
        ]);
    }
}

?>