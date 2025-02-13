<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logtime_Model extends LPTF_Model
{
    private $table = 'logtime';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
        $this->load->model('Logtime_Event_Model');
        $this->load->model('Promotion_Model');
        $this->load->model('Student_Model');
        $this->load->model('Calendar_Day_Model');
        $this->load->model('Followup_Model');
        $this->load->model('Alert_Model');
        $this->load->model('Absence_Model');
    }

    public function getRealLogtime($params)
    {
        $constraints = [
            ['logtime_id', 'optional', 'number', true],
            ['username', 'optional', 'string', true],
            ['student_id', 'optional', 'number'],
            ['day', 'optional', 'string'],
            ['algo1', 'optional', 'number'],
            ['algo2', 'optional', 'number'],
            ['algo3', 'optional', 'number'],
            ['promotion_id', 'optional', 'number', true],
            ['promotion_name', 'optional', 'string'],
            ['date_before', 'optional', 'string'],
            ['date_after', 'optional', 'string'],
            ['promotion_is_active', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getLogtimeFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);
        $query = $this->db->get($this->table);
        $logtimes_arr = $query->result_array();

        return $logtimes_arr;
    }

    public function getLogtime($params)
    {
        $constraints = [
            ['logtime_id', 'optional', 'number'],
            ['username', 'optional', 'string'],
            ['student_id', 'optional', 'number', true],
            ['day', 'optional', 'string'],
            ['algo1', 'optional', 'number'],
            ['algo2', 'optional', 'number'],
            ['algo3', 'optional', 'number'],
            ['promotion_id', 'optional', 'number', true],
            ['promotion_name', 'optional', 'string'],
            ['date_before', 'optional', 'string'],
            ['date_after', 'optional', 'string'],
            ['promotion_is_active', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $full_arr = [];
        $start_date = new DateTime(isset($params['date_after']) ? $params['date_after'] : $params['day']);
        $end_date = new DateTime(isset($params['date_before']) ? $params['date_before'] : $params['day']);

        while ($start_date <= $end_date) {
            $logtimes_arr = $this->getRealLogtime([
                'student_id' => isset($params['student_id']) ? $params['student_id'] : '',
                'promotion_id' => isset($params['promotion_id']) ? $params['promotion_id'] : '',
                'username' => '',
                'day' => $start_date->format('Y-m-d'),
                'algo1' => '',
                'algo2' => '',
                'algo3' => '',
            ]);
            if (count($logtimes_arr) > 0) {
                foreach ($logtimes_arr as $log) {
                    $logtime_event_arr = $this->Logtime_Event_Model->getLogtime_Event([
                        'student_id' => $log['logtime_student_id'],
                        'logtime_date' => $start_date->format('Y-m-d'),
                        'duration' => '',
                    ]);
                    if (count($logtime_event_arr) > 0) {
                        foreach ($logtime_event_arr as $log_event) {
                            $log['logtime_algo1'] = (int)$log['logtime_algo1'] + (int)$log_event['logtime_event_duration'];
                            $log['logtime_algo2'] = (int)$log['logtime_algo2'] + (int)$log_event['logtime_event_duration'];
                            $log['logtime_algo3'] = (int)$log['logtime_algo3'] + (int)$log_event['logtime_event_duration'];
                        }
                    }

                    array_push($full_arr, [ //Dans un soucis d'homogénéité de format, je reconstruis le tableau pour convertir en int les logtimes
                        'logtime_student_id' => $log['logtime_student_id'],
                        'promotion_id' => $log['promotion_id'],
                        'logtime_username' => $log['logtime_username'],
                        'logtime_day' => $start_date->format('Y-m-d'),
                        'logtime_algo1' => (int)$log['logtime_algo1'],
                        'logtime_algo2' => (int)$log['logtime_algo2'],
                        'logtime_algo3' => (int)$log['logtime_algo3'],
                    ]);
                }
            } else { //Pour les cas dans lesquels on n'a pas de ping dans la journée, mais du temps a été rajouté manuellement
                $logtime_event_arr = $this->Logtime_Event_Model->getLogtime_Event([
                    'student_id' => isset($params['student_id']) ? $params['student_id'] : '',
                    'promotion_id' => isset($params['promotion_id']) ? $params['promotion_id'] : '',
                    'username' => '',
                    'logtime_date' => $start_date->format('Y-m-d'),
                    'duration' => '',
                ]);
                if (count($logtime_event_arr) > 0) {
                    foreach ($logtime_event_arr as $log_event) {
                        array_push($full_arr, [
                            'logtime_student_id' => $log_event['student_id'],
                            'promotion_id' => $log_event['promotion_id'],
                            'logtime_username' => $log_event['username'],
                            'logtime_day' => $start_date->format('Y-m-d'),
                            'logtime_algo1' => (int)$log_event['logtime_event_duration'],
                            'logtime_algo2' => (int)$log_event['logtime_event_duration'],
                            'logtime_algo3' => (int)$log_event['logtime_event_duration'],
                        ]);
                    }
                }
                else {
                    array_push($full_arr, [
                        'logtime_student_id' => isset($params['student_id']) ? $params['student_id'] : '',
                        'promotion_id' => isset($params['promotion_id']) ? $params['promotion_id'] : '',
                        'logtime_username' => '',
                        'logtime_day' => $start_date->format('Y-m-d'),
                        'logtime_algo1' => 0,
                        'logtime_algo2' => 0,
                        'logtime_algo3' => 0,
                    ]);
                }
            }
            $start_date->modify('+1 day');
        }

        foreach ($full_arr as $field => $data) {
            if (!array_key_exists('promotion_id', $params)) unset($full_arr[$field]['promotion_id']);
            if (!array_key_exists('username', $params)) unset($full_arr[$field]['logtime_username']);
            if ($full_arr[$field]['logtime_algo1'] < 0) $full_arr[$field]['logtime_algo1'] = 0;
            if ($full_arr[$field]['logtime_algo2'] < 0) $full_arr[$field]['logtime_algo2'] = 0;
            if ($full_arr[$field]['logtime_algo3'] < 0) $full_arr[$field]['logtime_algo3'] = 0;
            if ($full_arr[$field]['logtime_algo1'] > 1440) $full_arr[$field]['logtime_algo1'] = 1440;
            if ($full_arr[$field]['logtime_algo2'] > 1440) $full_arr[$field]['logtime_algo2'] = 1440;
            if ($full_arr[$field]['logtime_algo3'] > 1440) $full_arr[$field]['logtime_algo3'] = 1440;
            if (!array_key_exists('algo1', $params)) unset($full_arr[$field]['logtime_algo1']);
            if (!array_key_exists('algo2', $params)) unset($full_arr[$field]['logtime_algo2']);
            if (!array_key_exists('algo3', $params)) unset($full_arr[$field]['logtime_algo3']);
        }
        return $full_arr;
    }

    public function getPromotionLogtime($params)
    {
        $constraints = [
            ['promotion_id', 'mandatory', 'number', true],
            ['logtime_id', 'optional', 'number'],
            ['student_id', 'optional', 'number'],
            ['day', 'optional', 'string'],
            ['algo1', 'optional', 'number'],
            ['algo2', 'optional', 'number'],
            ['algo3', 'optional', 'number'],
            ['username', 'optional', 'string'],
            ['promotion_name', 'optional', 'string'],
            ['date_before', 'optional', 'string'],
            ['date_after', 'optional', 'string'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getLogtimeFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function getStudentLastLogtime($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'],
            ['logtime_id', 'optional', 'number'],
            ['username', 'optional', 'string'],
            ['day', 'optional', 'string'],
            ['algo1', 'optional', 'number'],
            ['algo2', 'optional', 'number'],
            ['algo3', 'optional', 'number'],
            ['promotion_id', 'optional', 'number'],
            ['promotion_is_active', 'optional', 'boolean']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getLogtimeFields();
        $params['limit'] = '1';
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->order_by('logtime.day', 'desc')->get($this->table);

        return ($query->result_array());
    }

    public function getPromotionLastLogtime($params)
    {
        $constraints = [
            ['promotion_id', 'optional', 'number'],
            ['logtime_id', 'optional', 'number'],
            ['username', 'optional', 'string'],
            ['student_id', 'optional', 'number'],
            ['day', 'optional', 'string'],
            ['algo1', 'optional', 'number'],
            ['algo2', 'optional', 'number'],
            ['algo3', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->load->model('Student_Model');

        $params_students = [
            'promotion_id' => $params['promotion_id'],
            'promotion_name' => '',
            'student_id' => '',
        ];

        if (isset($params['promotion_id']) && !empty($params['promotion_id'])) {
            $params_students['promotion_id'] = $params['promotion_id'];
        } else {
            $params_students['promotion_is_active'] = '1';
        }

        $promotion_students = $this->Student_Model->getStudent($params_students);

        foreach ($promotion_students as $key => $student) {
            $student_last_log_params = [
                'student_id' => $student['student_id'],
                'logtime_id' => '',
                'username' => '',
                'day' => '',
                'algo1' => '',
                'algo2' => '',
                'algo3' => ''
            ];

            $last_log = $this->getStudentLastLogtime($student_last_log_params);
            if (count($last_log) > 0 && $student['student_id'] === $last_log[0]['logtime_student_id']) $promotion_students[$key]['logtime'] = $last_log[0];
        }

        return ($promotion_students);
    }

    public function getPromotionLogtimeAverage($params)
    {
        $constraints = [
            ['promotion_id', 'mandatory', 'number', true],
            ['date_before', 'optional', 'string'],
            ['date_after', 'optional', 'string'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $params['day'] = '';
        $params['algo1'] = '';
        $params['algo2'] = '';
        $params['algo3'] = '';

        $promotion_students_logtimes = $this->getLogtime($params);

        $promotion_logtimes_average = [];

        foreach ($promotion_students_logtimes as $key => $log) {
            if (!array_key_exists($log['logtime_day'], $promotion_logtimes_average)) {
                $promotion_logtimes_average[$log['logtime_day']] = [
                    'algo1' => $log['logtime_algo1'],
                    'algo2' => $log['logtime_algo2'],
                    'algo3' => $log['logtime_algo3'],
                    'day' => $log['logtime_day']
                ];
            } else {
                $promotion_logtimes_average[$log['logtime_day']]['algo1'] += $log['logtime_algo1'];
                $promotion_logtimes_average[$log['logtime_day']]['algo2'] += $log['logtime_algo2'];
                $promotion_logtimes_average[$log['logtime_day']]['algo3'] += $log['logtime_algo3'];
            }
        }

        $params_promotion_length = [
            'promotion_id' => $params['promotion_id'],
        ];
        $this->load->model('Student_Model');
        $promotion_length = count($this->Student_Model->getStudent($params_promotion_length));

        foreach ($promotion_logtimes_average as $key => $day) {
            $promotion_logtimes_average[$key]['avg_algo1'] = $day['algo1'] !=  0 ?  $day['algo1'] / $promotion_length : 0;
            $promotion_logtimes_average[$key]['avg_algo2'] = $day['algo2'] !=  0 ?  $day['algo2'] / $promotion_length : 0;
            $promotion_logtimes_average[$key]['avg_algo3'] = $day['algo3'] !=  0 ?  $day['algo3'] / $promotion_length : 0;
        }
        return ([$promotion_logtimes_average]);
    }

    private function getLogtimeFields()
    {
        return ([
            'logtime_id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'username' => [
                'type' => 'in',
                'field' => 'username',
                'filter' => 'where'
            ],
            'student_id' => [
                'type' => 'in',
                'field' => 'student_fk',
                'alias' => 'logtime_student_id',
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
            'algo1' => [
                'type' => 'in',
                'field' => 'algo1',
                'filter' => 'where'
            ],
            'algo2' => [
                'type' => 'in',
                'field' => 'algo2',
                'filter' => 'where'
            ],
            'algo3' => [
                'type' => 'in',
                'field' => 'algo3',
                'filter' => 'where'
            ],
            'promotion_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['logtime', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']],
                ],
                'alias' => 'promotion_id',
                'field' => 'id',
                'filter' => 'where',
            ],
            'promotion_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['logtime', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']],
                ],
                'alias' => 'promotion_name',
                'field' => 'name',
                'filter' => 'where',
            ],
            'promotion_is_active' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['logtime', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']],
                ],
                'alias' => 'promotion_is_active',
                'field' => 'is_active',
                'filter' => 'where',
            ],
        ]);
    }
}
