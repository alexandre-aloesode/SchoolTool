<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Calendar_Day_Model extends LPTF_Model
{
    private $table = 'calendar_day';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getCalendarDay($params)
    {
        $constraints = [
            ['id', 'optional', 'number', true], ['calendar_id', 'optional', 'number', true],
            ['day', 'optional', 'string', true], ['type', 'optional', 'number', true], ['date_before', 'optional', 'string'],
            ['date_after', 'optional', 'string'], ['promotion_id', 'optional', 'number', true],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getCalendarDayFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postCalendarDay($params)
    {
        if (isset($params['array'])) {
            $format = json_decode($params['array']);
            $result = [];
            foreach ($format as $day) {
                $data = [
                    'calendar_fk' => $day->calendar_id,
                    'day' => $day->date,
                    'type' => $day->type,
                ];
                $result[] = $data;
            }
            $response = $this->db->insert_batch($this->table, $result);

            return ($response);
        } else {
            $constraints = [
                ['calendar_id', 'mandatory', 'number'],
                ['day', 'mandatory', 'string'], ['type', 'mandatory', 'number']
            ];
            if ($this->api_helper->checkParameters($params, $constraints) == false) {
                return ($this->Status()->PreconditionFailed());
            }

            $data = [
                'calendar_fk' => $params['calendar_id'],
                'day' => $params['day'],
                'type' => $params['type'],
            ];

            $response = $this->db->insert($this->table, $data);

            return ($response === true ? $this->db->insert_id() : false);
        }
    }

    public function putCalendarDay($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number'], ['calendar_id', 'optional', 'number'],
            ['day', 'optional', 'string'], ['type', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['type', 'type'],
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
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

    public function deleteCalendarDay($params)
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

    private function getCalendarDayFields()
    {
        return ([
            'id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'calendar_id' => [
                'type' => 'in',
                'field' => 'calendar_fk',
                'alias' => 'calendar_id',
                'filter' => 'where'
            ],
            'day' => [
                'type' => 'in',
                'field' => 'day',
                'filter' => 'where'
            ],
            'date_after' => [
                'type' => 'filter',
                'field' => 'day >=',
                'filter' => 'where'
            ],
            'date_before' => [
                'type' => 'filter',
                'field' => 'day <=',
                'filter' => 'where'
            ],
            'type' => [
                'type' => 'in',
                'field' => 'type',
                'filter' => 'where'
            ],
            'promotion_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['calendar_day', 'calendar_fk'], 'right' => ['calendar', 'id']],
                    ['left' => ['calendar', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'alias' => 'promotion_id',
                'field' => 'id',
                'filter' => 'where',
            ],
        ]);
    }
}
