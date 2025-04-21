<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alert_Model extends LPTF_Model
{
    private $table = 'alert';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getAlert($params)
    {
        $constraints = [
            ['alert_id', 'optional', 'number', true], ['student_id', 'optional', 'number'],
            ['followup_id', 'optional', 'number'], ['alert_date', 'optional', 'string'],
            ['level', 'optional', 'number', true], ['status', 'optional', 'number', true],
            ['date_before', 'optional', 'string'], ['date_after', 'optional', 'string'],
            ['promotion_id', 'optional', 'number', true], ['promotion_name', 'optional', 'string'],
            ['promotion_is_active', 'optional', 'number'], ['student_email', 'optional', 'string'],
            ['applicant_id', 'optional', 'number'], ['current_unit_id', 'optional', 'number', true],
            ['followup_comment', 'optional', 'string'], ['followup_type', 'optional', 'string'],
            ['followup_author', 'optional', 'string'], ['followup_date', 'optional', 'string'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getAlertFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postAlert($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'],
            ['level', 'mandatory', 'number'], ['status', 'mandatory', 'number'],
            ['comment', 'mandatory', 'string']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->trans_start();

        $this->load->model('Student_Model');
        $student = $this->Student_Model->getStudent([
            'student_id' => $params['student_id'],
            'applicant_id' => ''
        ]);

        if (count($student) == 0) {
            return ($this->Status()->Error());
        }

        $this->load->model('Followup_Model');
        $followup = $this->Followup_Model->postFollowup([
            'applicant_id' => $student[0]['applicant_id'],
            'type' => 'ALERTE',
            'comment' => $params['comment'],
        ]);

        if ($followup === false) {
            return ($this->Status()->Error());
        }

        $todayDate = new DateTime();
        $data = [
            'student_fk' => $params['student_id'],
            'followup_fk' => $followup,
            'date' => $todayDate->format('Y-m-d'),
            'level' => $params['level'],
            'status' => $params['status'],
        ];

        $response = $this->db->insert($this->table, $data);
        if($response === true) $result = $this->db->insert_id(); 

        $this->db->trans_complete();

        return ($response === true ? $result : false);
    }

    public function putAlert($params)
    {
        $constraints = [
            ['alert_id', 'mandatory', 'number'], ['level', 'optional', 'number'], ['status', 'optional', 'number'],
            ['followup_id', 'optional', 'number'], ['followup_comment', 'optional', 'string'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }
        $data = [];
        $optional_fields = [
            ['level', 'level'],
            ['status', 'status'],
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        $this->db->trans_start();

        if (count($data) > 0) {
            $this->db->where('id', $params['alert_id']);
            $response = $this->db->update($this->table, $data);
        }

        if (isset($params['followup_id']) && isset($params['followup_comment'])) {
            $this->load->model('Followup_Model');
            $followup = $this->Followup_Model->getFollowup([
                'followup_id' => $params['followup_id'],
                'comment' => ''
            ]);

            if (count($followup) > 0) {
                $author = $this->token_helper->get_payload()['user_email'];
                $previous_comments = $followup[0]['followup_comment'];
                $level_to_string = $params['level'] == 1 ? "Avertissement" : ($params['level'] == 2 ? "RDV Péda" : "RAR");
                $status_to_string = $params['status'] == 0 ? "En cours" : ($params['status'] == 1 ? "Résolue" : "Non résolue");
                $new_comment = "<br/> <strong>" . date("j/n/Y") . " - " . $author . "</strong> <br/>Changement du statut en " . $level_to_string . " : " . $status_to_string . "<br/>Commentaire : " . $params['followup_comment'];
                $this->Followup_Model->putFollowup([
                    'followup_id' => $params['followup_id'],
                    'comment' => $previous_comments . "<br/>" . $new_comment,
                    'type' => 'ALERTE',
                ]);
            }
        }

        $this->db->trans_complete();

        return ($response);
    }

    public function deleteAlert($params)
    {
        $constraints = [
            ['alert_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->where_in('id', $params['alert_id']);
        $response = $this->db->delete($this->table);

        return ($response);
    }

    public function getAlertFollowup($params)
    {
        $this->load->model('Followup_Model');
        return $this->Followup_Model->getFollowup($params);
    }

    private function getAlertFields()
    {
        return ([
            'alert_id' => [
                'type' => 'in',
                'field' => 'id',
                'alias' => 'alert_id',
                'filter' => 'where'
            ],
            'student_id' => [
                'type' => 'in',
                'field' => 'student_fk',
                'alias' => 'student_id',
                'filter' => 'where'
            ],
            'student_email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alert', 'student_fk'], 'right' => ['student', 'id']],
                ],
                'alias' => 'student_email',
                'field' => 'email',
                'filter' => 'where',
            ],
            'followup_id' => [
                'type' => 'in',
                'field' => 'followup_fk',
                'alias' => 'followup_id',
                'filter' => 'where'
            ],
            'followup_comment' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alert', 'followup_fk'], 'right' => ['followup', 'id']],
                ],
                'alias' => 'followup_comment',
                'field' => 'comment',
                'filter' => 'where',
            ],
            'followup_type' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alert', 'followup_fk'], 'right' => ['followup', 'id']],
                ],
                'alias' => 'followup_type',
                'field' => 'type',
                'filter' => 'where',
            ],
            'followup_author' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alert', 'followup_fk'], 'right' => ['followup', 'id']],
                ],
                'alias' => 'followup_author',
                'field' => 'author',
                'filter' => 'where',
            ],
            'followup_date' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alert', 'followup_fk'], 'right' => ['followup', 'id']],
                ],
                'alias' => 'followup_date',
                'field' => 'creation_date',
                'filter' => 'where',
            ],
            'alert_date' => [
                'type' => 'in',
                'field' => 'date',
                'alias' => 'alert_date',
                'filter' => 'where'
            ],
            'level' => [
                'type' => 'in',
                'field' => 'level',
                'alias' => 'alert_level',
                'filter' => 'where'
            ],
            'status' => [
                'type' => 'in',
                'field' => 'status',
                'alias' => 'alert_status',
                'filter' => 'where'
            ],
            'date_after' => [
                'type' => 'filter',
                'field' => 'date >=',
                'filter' => 'where'
            ],
            'date_before' => [
                'type' => 'filter',
                'field' => 'date <=',
                'filter' => 'where'
            ],
            'promotion_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alert', 'student_fk'], 'right' => ['student', 'id']],
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
                    ['left' => ['alert', 'student_fk'], 'right' => ['student', 'id']],
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
                    ['left' => ['alert', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']],
                ],
                'alias' => 'promotion_is_active',
                'field' => 'is_active',
                'filter' => 'where',
            ],
            'applicant_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alert', 'student_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                ],
                'alias' => 'applicant_id',
                'field' => 'id',
                'filter' => 'where',
            ],
            'current_unit_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['alert', 'student_fk'], 'right' => ['student', 'id']],
                ],
                'alias' => 'current_unit_id',
                'field' => 'current_unit_fk',
                'filter' => 'where',
            ],
        ]);
    }
}
