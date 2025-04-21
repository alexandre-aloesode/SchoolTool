<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promotion_Unit_Model extends LPTF_Model
{
    private $table = 'promotion_unit';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getPromotionUnit($params)
    {
        $constraints = [
            ['id', 'optional', 'number'], ['unit_id', 'optional', 'number', true], ['promotion_id', 'optional', 'number'],
            ['unit_name', 'optional', 'string'], ['promotion_name', 'optional', 'string'],
            ['promotion_is_active', 'optional', 'boolean']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getPromotionUnitFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    } 

    public function postPromotionUnit($params)
    {
        $constraints = [
            ['promotion_id', 'mandatory', 'number'], ['unit_id', 'mandatory', 'number'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $data = [
            'unit_fk' => $params['unit_id'],
            'promotion_fk' => $params['promotion_id']
        ];

        $promotion_unit = $this->getPromotionUnit($params);
        if (count($promotion_unit) > 0)
        {
            return false;
        }

        $response = $this->db->insert($this->table, $data);
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function deletePromotionUnit($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number'], ['promotion_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $this->db->trans_start();

        $this->db->where('promotion_fk', $params['promotion_id']);
        $this->db->where("unit_fk", $params["unit_id"]);
        $response = $this->db->delete($this->table);
        $this->db->trans_complete();

        return ($response);
    }

    private function getPromotionUnitFields()
    {
        return([
            'id' => [
                'type'=>'int',
                'field' => 'id', 
                'alias' => 'id',
                'filter' => 'where',
            ],
            'unit_id' => [
                'type'=>'in',
                'field'=>'unit_fk',
                'alias' => 'unit_id',
                'filter'=>'where' 
            ],
            'promotion_id'=>[
                'type'=>'int',
                'field' => 'promotion_fk', 
                'alias' => 'promotion_id',
                'filter' => 'where',
            ],
            'unit_name'=>[
                'type'=>'out',
                'link' => [
                    ['left' => ['promotion_unit', 'unit_fk'], 'right' => ['unit', 'id']],
                ],
                'field' =>'name', 
                'alias' => 'unit_name',
                'filter' => 'like',
            ],
            'promotion_name'=>[
                'type'=>'out',
                'link' => [
                    ['left' => ['promotion_unit', 'promotion_fk'], 'right' => ['promotion', 'id']],
                ],
                'field' =>'name', 
                'alias' => 'promotion_name',
                'filter' => 'like',
            ],
            'promotion_is_active'=>[
                'type'=>'out',
                'link' => [
                    ['left' => ['promotion_unit', 'promotion_fk'], 'right' => ['promotion', 'id']],
                ],
                'field' =>'is_active', 
                'alias' => 'promotion_is_active',
                'filter' => 'where',
            ]
        ]);
    }
}

?>