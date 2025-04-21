<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Waiting_List_Model extends LPTF_Model
{
    private $table = 'waiting_list';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getWaitingList($params)
    {
        $constraints = [
            ['student_id', 'optional', 'number'], ['student_email', 'optional', 'string'],
            ['group_id', 'optional', 'number'], ['group_name', 'optional', 'string'],
            ['job_id', 'optional', 'number'], ['job_name', 'optional', 'string'],
            ['lead_email', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $fields = $this->getWaitingListFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postWaitingList($params)
    {
        $isok = $this->OkForTheJob($params);
        if ($isok !== true)
        {
            return ($this->Status()->Error());
        }

        $waiting = [
            'student_fk' => $params['student_id'],
            'registration_fk' => $params['group_id']
        ];
        $response = $this->db->insert($this->table, $waiting);
        
        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putWaitingList($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['group_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        // Verification qu'il y avait bien une demande
        $waitingspot = $this->getWaitingList($params);
        if (count($waitingspot) <= 0 || !$this->Status()->IsValid())
        {
            return ($this->Status()->Forbidden());
        }
        
        $tosub = [
            'student_id' => $params['student_id'],
            'group_id' => $params['group_id'] 
        ];
        $this->load->model('Registration_Model');
        $response = $this->Registration_Model->postMember($tosub);

        return ($response);
    }

    public function deleteWaitingList($params)
    {
        $constraints = [
            ['student_id', 'optional', 'number'], ['group_id', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $count = 0;
        if (array_key_exists('student_id', $params))
        {
            $this->db->where('student_fk', $params['student_id']);
            ++$count;
        }
        if (array_key_exists('group_id', $params))
        {
            $this->db->where('registration_fk', $params['group_id']);
            ++$count;
        }

        if ($count > 0)
        {
            $response = $this->db->delete($this->table);
            return ($response);
        }
        
        return ($this->Status()->NoContent());
    }

    public function deleteJobWaitingList($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['job_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $sql = "DELETE wl FROM waiting_list as wl 
            INNER JOIN registration
            ON registration.id = wl.registration_fk
            WHERE student_fk = ? AND registration.job_fk = ?";
        $response = $this->db->query($sql, [$params['student_id'], $params['job_id']]);

        return ($response);
    }

    public function deleteUnitWaitingList($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['unit_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        $sql = "DELETE wl FROM waiting_list as wl
            INNER JOIN registration as r ON r.id = wl.registration_fk
            INNER JOIN job as j on j.id = r.job_fk
            WHERE wl.student_fk = ? AND j.unit_fk = ?";
        $response = $this->db->query($sql, [$params['student_id'], $params['unit_id']]);

        return ($response);
    }

    private function OkForTheJob($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['group_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false)
        { return ($this->Status()->PreconditionFailed()); }

        // Recuperation de la reg du chef
        $reg_params = [
            'group_id' => $params['group_id'],
            'job_id' => '',
            'member_is_lead' => '1',
            'member_id' => '',
            'end_date' => '',
            'limit' => '1'
        ];
        $this->load->model('Registration_Model');
        $reg = $this->Registration_Model->GetRegistration($reg_params);
        if (count($reg) != 1 || !$this->Registration_Model->Status()->IsValid())
        {
            return ($this->Status()->Forbidden());
        }

        // Check si la date de fin du projet n'est pas dépassée
        $end_date = strtotime($reg[0]['end_date']);

        if ($reg[0]['end_date'] != null && $end_date < time())
        {
            return ($this->Status()->Forbidden());
        }

        // Check si le groupe est dispo pour le student
        $available_params = [
            'student_id' => $params['student_id'],
            'job_id' => $reg[0]['job_id']
        ];
        $groups = $this->Registration_Model->GetGroupAvailable($available_params);
        if (!$this->Registration_Model->Status()->IsValid())
        {
            return ($this->Status()->Forbidden());
        }

        $groupok = false;
        foreach ($groups as $group)
        {
            if (array_key_exists('group_id', $group) && $group['group_id'] == $params['group_id'])
            {
                $groupok = true;
                break ;
            }
        }

        if ($groupok == false)
        {
            return ($this->Status()->Forbidden());
        }

        return (true);
    }

    private function getWaitingListFields()
    {
        return ([
            'student_id' => [
                'type' => 'in', 
                'field' => 'student_fk',
                'alias' => 'student_id', 
                'filter' => 'where'
            ],
            'student_email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['waiting_list', 'student_fk'], 'right' => ['student', 'id']]
                ],
                'field' => 'email', 
                'alias' => 'student_email',
                'filter' => 'like',
            ],
            'group_id' => [
                'type' => 'in', 
                'field' => 'registration_fk',
                'alias' => 'group_id', 
                'filter' => 'where'
            ],
            'group_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['waiting_list', 'registration_fk'], 'right' =>['registration', 'id']]
                ],
                'field' => 'group_name', 
                'alias' => 'group_name',
                'filter' => 'like', 
            ],
            'job_id' => [
                'type' => 'out', 
                'link' => [
                    ['left' => ['waiting_list', 'registration_fk'], 'right' => ['registration', 'id']],
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']],
                ],
                'field' => 'id',
                'alias' => 'job_id', 
                'filter' => 'where'
            ],
            'job_name' => [
                'type' => 'out', 
                'link' => [
                    ['left' => ['waiting_list', 'registration_fk'], 'right' => ['registration', 'id']],
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']],
                ],
                'field' => 'name',
                'filter' => 'like'
            ],
            'lead_email' => [
                'type' => 'out', 
                'link' => [
                    ['left' => ['waiting_list', 'registration_fk'], 'right' => ['registration', 'id']],
                    ['left' => ['registration', 'lead_fk'], 'right' => ['student', 'id'], 'alias' => 'lead'],
                ],
                'field' => 'email',
                'filter' => 'like'
            ],
        ]);
    }
}

?>