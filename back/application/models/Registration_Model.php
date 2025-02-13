<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Registration_Model extends LPTF_Model
{
    private $table = 'registration';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getRegistration($params)
    {
        $constraints = [
            ['registration_id', 'optional', 'number'], ['group_id', 'optional', 'number'],
            ['group_name', 'optional', 'string'], ['member_is_lead', 'optional', 'boolean'],
            ['group_is_valid', 'optional', 'boolean'], ['job_is_done', 'optional', 'boolean'],
            ['job_is_complete', 'optional', 'boolean'], ['start_date', 'optional', 'none'],
            ['start_after', 'optional', 'string'], ['start_before', 'optional', 'string'],
            ['end_date', 'optional', 'none'], ['end_after', 'optional', 'string'],
            ['end_before', 'optional', 'string'], ['click_date', 'optional', 'string'],
            ['click_after', 'optional', 'string'], ['click_before', 'optional', 'string'],
            ['correction_date', 'optional', 'none'], ['lead_id', 'optional', 'number'],
            ['lead_email', 'optional', 'string'], ['member_id', 'optional', 'number', true],
            ['member_email', 'optional', 'string'], ['job_id', 'optional', 'number', true],
            ['job_name', 'optional', 'string'], ['job_duration', 'optional', 'number'],
            ['min_students', 'optional', 'number'], ['max_students', 'optional', 'number'],
            ['link_subject', 'optional', 'none'], ['job_description', 'optional', 'none'],
            ['job_unit_id', 'optional', 'number', true], ['job_unit_code', 'optional', 'string'],
            ['job_unit_name', 'optional', 'string'], ['job_unit_is_active', 'optional', 'string'],
            ['job_is_visible', 'optional', 'boolean'], ['job_is_success', 'optional', 'boolean'],
            ['comment', 'optional', 'none'], ['corrector', 'optional', 'string'],
            ['promotion_id', 'optional', 'number'], ['promotion_name', 'optional', 'string'],
            ['lead_github', 'optional', 'string'], ['lead_plesk', 'optional', 'string'],
            ['member_firstname', 'optional', 'string'], ['member_lastname', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getRegistrationFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function putRegistration($params)
    {
        $constraints = [
            ['registration_id', 'mandatory', 'number'], ['job_is_success', 'optional', 'boolean'],
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['is_success', 'job_is_success'],
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        if (count($data) > 0) {
            $this->db->where('id', $params['registration_id']);
            $response = $this->db->update($this->table, $data);
        } else {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function getJobStudent($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number'], ['student_id', 'optional', 'number'],
            ['student_firstname', 'optional', 'string'], ['student_lastname', 'optional', 'string'],
            ['student_email', 'optional', 'string'], ['group_id', 'optional', 'number'],
            ['group_name', 'optional', 'string'], ['group_is_valid', 'optional', 'boolean'],
            ['job_is_complete', 'optional', 'boolean']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getJobStudentFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        if ($asked_fields <= 1) {
            return ($this->Status()->PreconditionFailed());
        }

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function deleteJobStudent($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number'], ['student_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->where('job_fk', $params['job_id']);
        $this->db->where_in('member_fk', $params['student_id']);
        $response = $this->db->delete($this->table);

        return ($response);
    }

    public function getGroup($params)
    {
        $constraints = [
            ['group_id', 'mandatory', 'number'], ['group_name', 'optional', 'none'],
            ['start_date', 'optional', 'none'], ['end_date', 'optional', 'none'],
            ['click_date', 'optional', 'none'], ['correction_date', 'optional', 'none'],
            ['is_valid', 'optional', 'none'], ['is_done', 'optional', 'none'],
            ['is_complete', 'optional', 'none'], ['comment', 'optional', 'none'],
            ['corrector', 'optional', 'none'], ['lead_email', 'optional', 'none'],
            ['job_id', 'optional', 'none'], ['job_name', 'optional', 'none'],
            ['member', 'optional', 'none'], ['lead_github', 'optional', 'string'],
            ['lead_plesk', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getGroupFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);
        $result = $query->result_array();

        $allresult = [];
        $allstudents = [];

        if (count($result) > 0) {
            array_push($allresult, $result[0]);
        } else {
            return ([]);
        }

        if (isset($params['member'])) {
            $this->load->model('Student_Model');
            foreach ($result as $member => $table) {
                $params = [
                    'student_id' => $table['member'],
                    'email' => '',
                    'firstname' => '',
                    'lastname' => '',
                ];
                $stud = $this->Student_Model->getStudent($params);
                if ($this->Status()->IsValid()) {
                    array_push($allstudents, $stud[0]);
                }
            }
            $allresult[0]['member'] = $allstudents;
        }

        return ($allresult);
    }

    public function postGroup($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number'], ['student_id', 'mandatory', 'number'],
            ['group_name', 'mandatory', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        if (strlen($params['group_name']) < 5 || strlen($params['group_name']) > 30) {
            return ($this->Status()->PreconditionFailed());
        }

        // Data initiale
        $data = [
            'lead_fk' => $params['student_id'],
            'member_fk' => $params['student_id'],
            'group_name' => $params['group_name'],
            'job_fk' => $params['job_id'],
            'is_lead' => 1,
        ];

        // Récupération du Job
        $query_job = [
            'job_id' => $params['job_id'],
            'min_students' => '',
            'duration' => ''
        ];

        $this->load->model('Job_Model');
        $job = $this->Job_Model->getJob($query_job);

        if (count($job) != 1) {
            return ($this->Status()->Forbidden());
        }

        $job = $job[0];

        // Vérification si le nombre d'étudiant minimum pour le Job est égal à 1
        if ($job['job_min_students'] == 1) {
            date_default_timezone_set('Europe/Paris');
            $duration = $job['job_duration'];
            $start_date = Date('Y-m-d H:i:s');
            $end_date = Date('Y-m-d H-i-s', strtotime($start_date . " + $duration days"));

            $data['start_date'] = $start_date;
            $data['end_date'] = $end_date;
            $data['is_valid'] = 1;
        }

        $this->db->trans_start();
        $response = $this->db->insert($this->table, $data);
        if ($response == true)
            $group_id = $this->db->insert_id();

        // Remplacement du 'group_id' par l'id de la Registration
        $this->db->set('group_id', $group_id);
        $this->db->where('id', $group_id);
        $this->db->update($this->table);

        // Insertion des acquired skill
        $this->db->select('id');
        $this->db->where('job_fk', $params['job_id']);
        $query = $this->db->get('job_skill');
        $jobskills = $query->result_array();

        foreach ($jobskills as $value) {
            $data = [
                'status' => 'En cours',
                'job_skill_fk' => $value['id'],
                'registration_fk' => $group_id,
                'student_fk' => $params['student_id'],
            ];

            $this->db->insert('acquiered_skill', $data);
        }

        // Nettoyage de la waiting list
        $todel = [
            'student_id' => $params['student_id'],
            'job_id' => $params['job_id']
        ];
        $this->load->model('Waiting_List_Model');
        $this->Waiting_List_Model->deleteJobWaitingList($todel);
        $this->db->trans_complete();

        // On retourne le 'group_id en réponse'
        return ($group_id);
    }

    public function putGroup($params)
    {
        $constraints = [
            ['group_id', 'mandatory', 'number'], ['group_name', 'mandatory', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        if (strlen($params['group_name']) < 5 || strlen($params['group_name']) > 30) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['group_name', 'group_name']
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        if (count($data) > 0) {
            $this->db->where('group_id', $params['group_id']);
            $response = $this->db->update($this->table, $data);
        } else {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteGroup($params)
    {
        $constraints = [
            ['group_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->where_in('group_id', $params['group_id']);
        $response = $this->db->delete($this->table);

        return ($response);
    }

    public function putGroupValidity($params)
    {
        $constraints = [
            ['group_id', 'mandatory', 'number'], ['validity', 'mandatory', 'boolean']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        if ($params['validity'] == 1) {
            $query_reg = [
                'group_id' => $params['group_id'],
                'job_duration' => '',
                'group_is_valid' => '',
                'job_unit_id' => '',
                'limit' => '1'
            ];

            $regs = $this->getRegistration($query_reg);
            if (count($regs) != 1) {
                return ($this->Status()->Forbidden());
            }

            if ($regs[0]['group_is_valid'] == '1') {
                return (true);
            }

            $start_date = date('Y-m-d H:i:s');
            $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' + ' . $regs[0]['job_duration'] . ' days'));

            $this->load->model('Unit_Model');
            $unit_data = [
                'unit_id' => $regs[0]['job_unit_id'],
                'end_date' => '',
            ];
            $unit = $this->Unit_Model->getUnit($unit_data);

            if (isset($unit[0]['unit_end_date']) && $unit[0]['unit_end_date'] < $end_date) {
                $end_date = $unit[0]['unit_end_date'];
            }

            $data = [
                'is_valid' => '1',
                'start_date' => $start_date,
                'end_date' => $end_date
            ];
        } else {
            $data = [
                'is_valid' => 0,
                'is_complete' => 0,
                'is_done' => 0,
                'start_date' => NULL,
                'end_date' => NULL,
                'click_date' => NULL,
                'comment' => NULL,
                'corrector' => NULL,
                'correction_date' => NULL
            ];
        }

        $this->db->trans_start();
        $this->db->where('group_id', $params['group_id']);
        $response = $this->db->update($this->table, $data);

        $groupid = $params['group_id'];
        $this->db->query("update acquiered_skill as ask
            inner join registration as r
            on r.id = ask.registration_fk
            set ask.status = 'En cours'
            where r.group_id = $groupid");
        $this->db->trans_complete();

        return ($response);
    }

    public function getGroupReview($params)
    {
        $constraints = [
            ['group_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $data['group_id'] = $params['group_id'];
        $data['member_is_lead'] = '1';
        $data['lead_id'] = '';
        $data['group_name'] = '';
        $data['job_id'] = '';
        $data['job_name'] = '';
        $data['job_unit_id'] = '';
        $data['job_unit_name'] = '';
        $data['corrector'] = '';
        $data['start_date'] = '';
        $data['end_date'] = '';
        $data['click_date'] = '';
        $data['correction_date'] = '';
        $data['comment'] = '';

        $group_infos = $this->getRegistration($data);

        if (count($group_infos) != 1) {
            return ($this->Status()->Forbidden());
        }

        $data = [];
        $data['student_id'] = $group_infos[0]['lead_id'];
        $data['group_id'] = $group_infos[0]['group_id'];
        $data['skill_id'] = '';
        $data['skill_name'] = '';
        $data['job_skill_earned'] = '';

        $this->load->model('Acquiered_Skill_Model');
        $skill_infos = $this->Acquiered_Skill_Model->getStudentSkill($data);

        $result = $group_infos[0];
        unset($result['member_is_lead']);
        unset($result['lead_id']);

        foreach ($skill_infos as $key => $infos) {
            unset($skill_infos[$key]['student_id']);
            unset($skill_infos[$key]['group_id']);
        }

        $result['skill'] = $skill_infos;

        return ($result);
    }

    public function putGroupReview($params)
    {
        $constraints = [
            ['group_id', 'mandatory', 'number'], ['comment', 'mandatory', 'string'],
            ['skills', 'optional', 'array', 'other'], ['complete', 'mandatory', 'boolean']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->trans_start();
        // Récupération des informations du groupe
        $groupdata = [
            'group_id' => $params['group_id'],
            'group_name' => "",
            'start_date' => "",
            'end_date' => "",
            'click_date' => "",
            'correction_date' => "",
            'is_valid' => "",
            'is_done' => "",
            'is_complete' => "",
            'lead_email' => "",
            'member' => "",
            'job_id' => "",
            'job_name' => "",
            'comment' => "",
            'corrector' => ""
        ];
        $group = $this->GetGroup($groupdata);

        if (!$this->Status()->IsValid()) {
            return ($this->Status()->PreconditionFailed());
        }

        // Validation du groupe s'il n'est pas déjà validé
        if (!$group[0]["is_valid"]) {
            $data = [
                'group_id' => $params['group_id'],
                'validity' => '1'
            ];
            $this->putGroupValidity($data);
            if (!$this->Status()->IsValid()) {
                return ($this->Status()->PreconditionFailed());
            }
        }

        // Modification de tous les skills de tous les membres
        $this->load->model('Acquiered_Skill_Model');
        foreach ($group[0]["member"] as $member) {
            if (isset($params["skills"])) {
                foreach ($params["skills"] as $skill) {
                    if ($skill["status"] === 'En cours' && $params['complete'] == 1) // skill_status
                    {
                        return ($this->Status()->PreconditionFailed());
                    }
                    $skilldata = [
                        "student_id" => $member["student_id"],
                        "job_id" => $group[0]["job_id"],
                        "skill_id" => $skill["skill_id"],
                        "status" => $skill["status"]
                    ];
                    $this->Acquiered_Skill_Model->putStudentSkill($skilldata, false);
                    if (!$this->Status()->IsValid()) {
                        return ($this->Status()->PreconditionFailed());
                    }
                }
            }
        }

        // Modification de la registration
        if ($params['complete'] == 0)
            $group[0]['click_date'] = null;
        else if ($group[0]['click_date'] == null)
            $group[0]['click_date'] = date("Y-m-d H:i:s");

        if (!isset($params['complete']) || $params['complete'] != 0)
            $params['complete'] = 1;
        $data = [
            "is_done" => 1,
            "comment" => $params['comment'],
            "corrector" => $this->token_helper->get_payload()["user_email"],
            "correction_date" => date("Y-m-d H:i:s"),
            "click_date" => $group[0]['click_date'],
            "is_complete" => $params['complete']
        ];
        $this->db->where('group_id', $params['group_id']);
        $response = $this->db->update($this->table, $data);

        // Check des units goals
        if ($params['complete'] == 1) {
            $this->load->model('Unit_Goal_Model');

            foreach ($group[0]["member"] as $member) {
                $data_check_unit_goal = [
                    "student_id" => $member['student_id'],
                ];
                $this->Unit_Goal_Model->getUnitGoalStudent($data_check_unit_goal);
            }
        }
        //Update de is_success dans registration si le nombre de points est supérieur à 50%
        $regData = [
            'registration_id' => '',
            'group_id' => $params['group_id'],
            'job_is_complete' => '1',
            'job_is_success' => '0'
        ];
        $groups_array = $this->getRegistration($regData);

        foreach ($groups_array as $group) {
            $review = $this->getGroupReview(['group_id' => $group['group_id']]);
            $review['total_points'] = 0;
            $review['total_points_earned'] = 0;
            foreach ($review['skill'] as $key => $value) {
                $review['total_points'] += (int)$value['job_skill_points'];
                $review['total_points_earned'] += (int)$value['job_skill_earned'];
            }
            $trans = $this->putRegistration([
                'registration_id' => $group['registration_id'],
                'job_is_success' => $review['total_points_earned'] > 0 && $review['total_points_earned'] / $review['total_points'] >= 0.5 ?
                    '1' : '0'
            ]);
        }

        $this->db->trans_complete();

        return $response;
    }

    public function postMember($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['group_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        //recuperer jobid + minstudents
        $this->db->select('group_id, group_name, is_lead, is_valid, job_fk, min_students, max_students');
        $this->db->select('is_done, is_complete, start_date, end_date, click_date');
        $this->db->select('lead_fk, member_fk');
        $this->db->join("job", "job.id = registration.job_fk");
        $this->db->where('group_id', $params['group_id']);
        $response = $this->db->get($this->table);
        $regs = $response->result_array();

        if (count($regs) <= 0) {
            return ($this->Status()->Forbidden());
        }

        $job = $regs[0];
        $job_id = $job['job_fk'];

        // check si le job est pas deja fini
        if ($job['click_date'] != null || $job['is_done'] == '1') {
            return ($this->Status()->Forbidden());
        }

        // insertion
        $data = [
            'group_id' => $job['group_id'],
            'group_name' => $job['group_name'],
            'is_lead' => 0,
            'is_valid' => $job['is_valid'],
            'is_done' => $job['is_done'],
            'is_complete' => $job['is_complete'],
            'start_date' => $job['start_date'],
            'end_date' => $job['end_date'],
            'click_date' => $job['click_date'],
            'lead_fk' => $job['lead_fk'],
            'member_fk' => $params['student_id'],
            'job_fk' => $job['job_fk']
        ];

        $this->load->model('Waiting_List_Model');

        $this->db->trans_start();

        $response = $this->db->insert($this->table, $data);
        if ($response == true)
            $new_id = $this->db->insert_id();

        if (count($regs) + 1 == intval($job['min_students'])) {
            $datavalidity = [
                'group_id' => $params['group_id'],
                'validity' => '1'
            ];
            $data['is_valid'] = "1";
            $this->putGroupValidity($datavalidity);
        }

        // ajout des jobs skills
        $this->db->select('id');
        $this->db->where('job_fk', $job_id);
        $query = $this->db->get('job_skill');
        $jobskills = $query->result_array();

        foreach ($jobskills as $value) {
            $data = [
                'status' => 'En cours',
                'job_skill_fk' => $value['id'],
                'registration_fk' => $new_id,
                'student_fk' => $params['student_id'],
            ];

            $this->db->insert('acquiered_skill', $data);
        }

        // Nettoyage de la waiting list
        if (count($regs) + 1 == intval($job['max_students'])) {
            $todel = [
                'group_id' => $params['group_id']
            ];
            $this->Waiting_List_Model->deleteWaitingList($todel);
        }
        $todel = [
            'student_id' => $params['student_id'],
            'job_id' => $job_id
        ];
        $this->Waiting_List_Model->deleteJobWaitingList($todel);
        $this->db->trans_complete();

        return ($response === true ? $new_id : false);
    }

    public function deleteMember($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'], ['group_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        //recup  info group
        $query_member = [
            'group_id' => $params['group_id'],
            'registration_id' => '',
            'member_id' => $params['student_id'],
            'lead_id' => '',
            'job_id' => '',
            'member_is_lead' => ''
        ];

        $data = $this->Registration_Model->getRegistration($query_member);
        if (count($data) != 1) {
            return ($this->Status()->Forbidden());
        }

        $group_id = $params['group_id'];
        $lead_id = $data[0]['lead_id'];
        $member_id = $params['student_id'];
        $reg_id = $data[0]['registration_id'];
        $job_id = $data[0]['job_id'];
        $is_lead = $data[0]['member_is_lead'];

        //suppression du member
        $this->db->trans_start();
        $this->db->where('member_fk', $params['student_id']);
        $this->db->where('group_id', $params['group_id']);
        $response = $this->db->delete($this->table);

        if ($member_id != $lead_id) {
            $this->db->trans_complete();
            return ($response);
        }

        //recup les members restant et transfert le lead 
        $query_members = [
            'group_id' => $group_id,
            'member_id' => '',
            'registration_id' => ''
        ];

        $data = $this->Registration_Model->getRegistration($query_members);
        if (count($data) <= 0) {
            $this->db->trans_complete();
            return ($response);
        }

        $member_id = $data[0]['member_id'];
        $member_reg = $data[0]['registration_id'];

        $create_leader = [
            'is_lead' => 1
        ];

        //definit le membre en leader
        $this->db->where('member_fk', $member_id);
        $this->db->where('group_id', $group_id);
        $response = $this->db->update($this->table, $create_leader);

        // set le leader à tout les members
        $create_leader = [
            'lead_fk' => $member_id,
            'group_id' => $member_reg
        ];

        $this->db->where('group_id', $group_id);
        $response = $this->db->update($this->table, $create_leader);
        $this->db->trans_complete();

        return ($response);
    }

    public function getGroupAvailable($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number'], ['student_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        // Vérification des prérequis pour le Job
        $this->load->model('Job_Model');
        $data = [
            'student_id' => $params['student_id'],
            'job_id' => $params['job_id']
        ];
        $job = $this->Job_Model->getStudentJobAvailable($data);

        if (count($job) <= 0) {
            return ($this->Status()->Forbidden());
        }
        if ($job[0]['prerequisites'] == 0) {
            return ($this->Status()->Forbidden());
        }

        // Vérification si le groupe n'est pas full
        $data = [
            'job_fk' => $params['job_id'],
            'click_date' => null,
            'is_done' => 0
        ];

        $this->db->select('s.email, a.firstname, a.lastname, group_id, group_name, job.id, job.name, job.max_students');
        $this->db->select('is_lead');
        $this->db->join('job', 'job.id = registration.job_fk');
        $this->db->join('student as s', 's.id = registration.lead_fk');
        $this->db->join('applicant as a', 'a.id = s.applicant_fk');
        $this->db->where($data);
        $query = $this->db->get($this->table);
        $regs = $query->result_array();
        if (!$this->Status()->IsValid()) {
            return ($this->Status()->Forbidden());
        }

        // Recup des waiting list de l'etudiants
        $wlparams = [
            'student_id' => $params['student_id'],
            'job_id' => $params['job_id'],
            'group_id' => ''
        ];
        $this->load->model('Waiting_List_Model');
        $wls = $this->Waiting_List_Model->getWaitingList($wlparams);
        if (!$this->Status()->IsValid()) {
            return ($this->Status()->Forbidden());
        }

        $response = [];
        foreach ($regs as $group) {
            $countmembers = 0;
            foreach ($regs as $reg) {
                if ($reg['group_id'] == $group['group_id']) {
                    ++$countmembers;
                }
            }

            $found = false;
            foreach ($wls as $wl) {
                if ($wl['group_id'] == $group['group_id']) {
                    $found = true;
                }
            }

            if ($found == false && $group['is_lead'] == 1 && $countmembers < intval($group['max_students'])) {
                $group_response = [
                    'job_id' => $group['id'],
                    'job_name' => $group['name'],
                    'group_id' => $group['group_id'],
                    'group_name' => $group['group_name'],
                    'lead_email' => $group['email'],
                    'lead_firstname' => $group['firstname'],
                    'lead_lastname' => $group['lastname']
                ];
                array_push($response, $group_response);
            }
        }

        return ($response);
    }

    public function putGroupClick($params)
    {
        $constraints = [
            ['group_id', 'mandatory', 'number'], ['click', 'mandatory', 'boolean']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->trans_start();
        $data = [
            'group_id' => $params['group_id'],
            'group_is_valid' => '',
            'click_date' => '',
            'limit' => 1
        ];
        $reg = $this->getRegistration($data);

        if (count($reg) <= 0) {
            return ($this->Status()->Forbidden());
        }

        if ($params['click'] == '0') {
            $this->db->set('click_date', null);
        } else if ($params['click'] == '1') {
            if ($reg[0]['click_date'] != null) {
                return ($this->Status()->Forbidden());
            }
            date_default_timezone_set('Europe/Paris');
            $click_date = Date('Y-m-d H:i:s');
            $this->db->set('click_date', $click_date);
        } else {
            return ($this->Status()->Forbidden());
        }

        $where = [
            'group_id' => $params['group_id']
        ];

        $this->db->where($where);
        $response = $this->db->update($this->table);

        if ($params['click'] == '1') {
            if ($reg[0]['group_is_valid'] == '0') {
                $validityparams = [
                    'group_id' => $params['group_id'],
                    'validity' => '1'
                ];
                $this->PutGroupValidity($validityparams);
            }
            // Nettoyage de la waiting list
            $this->load->model('Waiting_List_Model');
            $todel = [
                'group_id' => $params['group_id']
            ];
            $this->Waiting_List_Model->DeleteWaitingList($todel);
        }
        $this->db->trans_complete();

        return ($response);
    }

    public function getJobCorrector($params)
    {
        $this->db->select('corrector');
        $this->db->distinct();
        $this->db->where(['is_done' => 1]);
        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function putJobReview($params)
    {
        $constraints = [
            ['job_id', 'mandatory', 'number'], ['comment', 'mandatory', 'string'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->trans_start();
        // Récupération des informations du groupe

        $job_skills_params = [
            'job_id' => $params['job_id'],
            'skill_id' => '',
            'skill_name' => '',
        ];

        $all_regs_params = [
            'job_id' => $params['job_id'],
            'member_is_lead' => "1",
            'group_id' => '',
            'click_date' => '',
            'job_is_complete' => '0',
            'member_id' => '',
        ];

        $all_regs = $this->getRegistration($all_regs_params);

        foreach ($all_regs as $reg) {

            if ($reg['click_date'] != null) {
                $this->load->model('Acquiered_Skill_Model');
                $get_reg_skills_params = [
                    'student_id' => $reg['member_id'],
                    'group_id' => $reg['group_id'],
                    'skill_id' => ''
                ];
                $get_reg_skills = $this->Acquiered_Skill_Model->getStudentSkill($get_reg_skills_params);

                $skills = [];
                foreach ($get_reg_skills as $skill) {
                    array_push($skills, ['skill_id' => $skill['skill_id'], 'status' => 'Pro']);
                }
                $put_review_data = [
                    'group_id' => $reg['group_id'],
                    'comment' => $params['comment'],
                    'skills' => $skills,
                    'complete' => '1'
                ];

                $result = $this->putGroupReview($put_review_data);
            }
        }

        $this->db->trans_complete();
        return (true);
    }

    public function putUnitReview($params)
    {
        $constraints = [
            ['unit_id', 'mandatory', 'number'], ['comment', 'mandatory', 'string'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->trans_start();

        $this->load->model('Job_Model');

        $unit_jobs_params = [
            'unit_id' => $params['unit_id'],
            'job_id' => '',

        ];

        $unit_jobs = $this->Job_Model->getJob($unit_jobs_params);

        foreach ($unit_jobs as $job) {
            $job_review_data = [
                'job_id' => $job['job_id'],
                'comment' => $params['comment']
            ];

            $result = $this->putJobReview($job_review_data);
        }

        $this->db->trans_complete();
        return ($result);
    }

    // public function getPromotionRegistration($params)
    // {
    //     $constraints = [
    //         ['promotion_id', 'mandatory', 'number'], ['registration_id', 'optional', 'number'],
    //         ['group_name', 'optional', 'string'], ['member_is_lead', 'optional', 'boolean'],
    //         ['group_is_valid', 'optional', 'boolean'], ['job_is_done', 'optional', 'boolean'],
    //         ['job_is_complete', 'optional', 'boolean'], ['start_date', 'optional', 'none'],
    //         ['start_after', 'optional', 'string'], ['start_before', 'optional', 'string'],
    //         ['end_date', 'optional', 'none'], ['end_after', 'optional', 'string'],
    //         ['end_before', 'optional', 'string'], ['click_date', 'optional', 'string'],
    //         ['click_after', 'optional', 'string'], ['click_before', 'optional', 'string'],
    //         ['correction_date', 'optional', 'none'], ['lead_id', 'optional', 'number'],
    //         ['lead_email', 'optional', 'string'], ['member_id', 'optional', 'number'],
    //         ['member_email', 'optional', 'string'], ['job_id', 'optional', 'number'],
    //         ['job_name', 'optional', 'string'], ['job_duration', 'optional', 'number'],
    //         ['min_students', 'optional', 'number'], ['max_students', 'optional', 'number'],
    //         ['link_subject', 'optional', 'none'], ['job_description', 'optional', 'none'],
    //         ['job_unit_id', 'optional', 'number', true], ['job_unit_code', 'optional', 'string'],
    //         ['job_unit_name', 'optional', 'string'], ['comment', 'optional', 'none'],
    //         ['corrector', 'optional', 'string'], ['group_id', 'optional', 'number']
    //     ];
    //     if ($this->api_helper->checkParameters($params, $constraints) == false)
    //     { return ($this->Status()->PreconditionFailed()); }

    //     $fields = $this->getRegistrationFields();
    //     $asked_fields = $this->api_helper->buildGet($params, $fields);
    //     $this->api_helper->addLimitAndOffset($params);

    //     $query = $this->db->get($this->table);

    //     return ($query->result_array());
    // }

    private function getRegistrationFields()
    {
        return ([
            'registration_id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'group_id' => [
                'type' => 'in',
                'field' => 'group_id',
                'alias' => 'group_id',
                'filter' => 'where'
            ],
            'group_name' => [
                'type' => 'in',
                'field' => 'group_name',
                'alias' => 'group_name',
                'filter' => 'like'
            ],
            'member_is_lead' => [
                'type' => 'in',
                'field' => 'is_lead',
                'alias' => 'member_is_lead',
                'filter' => 'where'
            ],
            'group_is_valid' => [
                'type' => 'in',
                'field' => 'is_valid',
                'alias' => 'group_is_valid',
                'filter' => 'where'
            ],
            'job_is_done' => [
                'type' => 'in',
                'field' => 'is_done',
                'alias' => 'job_is_done',
                'filter' => 'where'
            ],
            'job_is_complete' => [
                'type' => 'in',
                'field' => 'is_complete',
                'alias' => 'job_is_complete',
                'filter' => 'where'
            ],
            'job_is_complete' => [
                'type' => 'in',
                'field' => 'is_complete',
                'alias' => 'job_is_complete',
                'filter' => 'where'
            ],
            'job_is_success' => [
                'type' => 'in',
                'field' => 'is_success',
                'alias' => 'job_is_success',
                'filter' => 'none'
            ],
            'start_date' => [
                'type' => 'in',
                'field' => 'start_date',
                'alias' => 'start_date',
                'filter' => 'none'
            ],
            'start_after' => [
                'type' => 'filter',
                'field' => 'start_date >=',
                'filter' => 'where'
            ],
            'start_before' => [
                'type' => 'filter',
                'field' => 'start_date <=',
                'filter' => 'where'
            ],
            'end_date' => [
                'type' => 'in',
                'field' => 'end_date',
                'alias' => 'end_date',
                'filter' => 'none'
            ],
            'end_after' => [
                'type' => 'filter',
                'field' => 'end_date >=',
                'filter' => 'where'
            ],
            'end_before' => [
                'type' => 'filter',
                'field' => 'end_date <=',
                'filter' => 'where'
            ],
            'click_date' => [
                'type' => 'in',
                'field' => 'click_date',
                'alias' => 'click_date',
                'filter' => 'where'
            ],
            'click_after' => [
                'type' => 'filter',
                'field' => 'click_date >=',
                'filter' => 'where'
            ],
            'click_before' => [
                'type' => 'filter',
                'field' => 'click_date <=',
                'filter' => 'where'
            ],
            'correction_date' => [
                'type' => 'in',
                'field' => 'correction_date',
                'alias' => 'correction_date',
                'filter' => 'none'
            ],
            'lead_id' => [
                'type' => 'in',
                'field' => 'lead_fk',
                'alias' => 'lead_id',
                'filter' => 'where'
            ],
            'lead_email' => [
                'type' => 'out',
                'field' => 'email',
                'link' => [
                    ['left' => ['registration', 'lead_fk'], 'right' => ['student', 'id']]
                ],
                'alias' => 'lead_email',
                'filter' => 'like'
            ],
            'member_id' => [
                'type' => 'in',
                'field' => 'member_fk',
                'alias' => 'member_id',
                'filter' => 'where'
            ],
            'member_email' => [
                'type' => 'out',
                'field' => 'email',
                'link' => [
                    ['left' => ['registration', 'member_fk'], 'right' => ['student', 'id'], 'alias' => 'member']
                ],
                'alias' => 'member_email',
                'filter' => 'like'
            ],
            'member_firstname' => [
                'type' => 'out',
                'field' => 'firstname',
                'link' => [
                    ['left' => ['registration', 'member_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'alias' => 'member_firstname',
                'filter' => 'where'
            ],
            'member_lastname' => [
                'type' => 'out',
                'field' => 'lastname',
                'link' => [
                    ['left' => ['registration', 'member_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'alias' => 'member_lastname',
                'filter' => 'where'
            ],
            'job_id' => [
                'type' => 'in',
                'field' => 'job_fk',
                'alias' => 'job_id',
                'filter' => 'where'
            ],
            'job_name' => [
                'type' => 'out',
                'field' => 'name',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']]
                ],
                'filter' => 'like'
            ],
            'job_is_visible' => [
                'type' => 'out',
                'field' => 'is_visible',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']]
                ],
                'filter' => 'like'
            ],
            'job_code' => [
                'type' => 'out',
                'field' => 'code',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']]
                ],
                'filter' => 'like'
            ],
            'job_duration' => [
                'type' => 'out',
                'field' => 'duration',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']]
                ],
                'filter' => 'where'
            ],
            'min_students' => [
                'type' => 'out',
                'field' => 'min_students',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']]
                ],
                'filter' => 'where'
            ],
            'max_students' => [
                'type' => 'out',
                'field' => 'max_students',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']]
                ],
                'filter' => 'where'
            ],
            'link_subject' => [
                'type' => 'out',
                'field' => 'link_subject',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']]
                ],
                'filter' => 'none'
            ],
            'job_description' => [
                'type' => 'out',
                'field' => 'description',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']]
                ],
                'filter' => 'none'
            ],
            'job_unit_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']]
                ],
                'field' => 'unit_fk',
                'alias' => 'job_unit_id',
                'filter' => 'where'
            ],
            'job_unit_code' => [
                'type' => 'out',
                'field' => 'code',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']],
                    ['left' => ['job', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'alias' => 'job_unit_code',
                'filter' => 'where'
            ],
            'job_unit_name' => [
                'type' => 'out',
                'field' => 'name',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']],
                    ['left' => ['job', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'alias' => 'job_unit_name',
                'filter' => 'where'
            ],
            'job_unit_is_active' => [
                'type' => 'out',
                'field' => 'is_active',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']],
                    ['left' => ['job', 'unit_fk'], 'right' => ['unit', 'id']]
                ],
                'alias' => 'job_unit_is_active',
                'filter' => 'where'
            ],
            'comment' => [
                'type' => 'in',
                'field' => 'comment',
                'alias' => 'comment',
                'filter' => 'none'
            ],
            'corrector' => [
                'type' => 'in',
                'field' => 'corrector',
                'alias' => 'corrector',
                'filter' => 'like'
            ],
            'promotion_id' => [
                'type' => 'out',
                'field' => 'promotion_fk',
                'link' => [
                    ['left' => ['registration', 'member_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                ],
                'alias' => 'promotion_id',
                'filter' => 'where'
            ],
            'promotion_name' => [
                'type' => 'out',
                'field' => 'name',
                'link' => [
                    ['left' => ['registration', 'member_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']],
                ],
                'alias' => 'promotion_name',
                'filter' => 'where'
            ],
            'lead_github' => [
                'type' => 'out',
                'field' => 'github',
                'link' => [
                    ['left' => ['registration', 'lead_fk'], 'right' => ['student', 'id']]
                ],
                'alias' => 'lead_github',
                'filter' => 'where'
            ],
            'lead_plesk' => [
                'type' => 'out',
                'field' => 'plesk',
                'link' => [
                    ['left' => ['registration', 'lead_fk'], 'right' => ['student', 'id']]
                ],
                'alias' => 'lead_plesk',
                'filter' => 'where'
            ],
        ]);
    }

    private function getJobStudentFields()
    {
        return ([
            'job_id' => [
                'type' => 'in',
                'field' => 'job_fk',
                'alias' => 'job_id',
                'filter' => 'where',
            ],
            'student_id' => [
                'type' => 'in',
                'field' => 'member_fk',
                'alias' => 'student_id',
                'filter' => 'where'
            ],
            'student_firstname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['registration', 'member_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]

                ],
                'field' => 'firstname',
                'alias' => 'student_firstname',
                'filter' => 'like'
            ],
            'student_lastname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['registration', 'member_fk'], 'right' => ['student', 'id']],
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' => 'lastname',
                'alias' => 'student_lastname',
                'filter' => 'like'
            ],
            'student_email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['registration', 'member_fk'], 'right' => ['student', 'id']]
                ],
                'field' => 'email',
                'filter' => 'like'

            ],
            'group_id' => [
                'type' => 'in',
                'field' => 'group_id',
                'alias' => 'group_id',
                'filter' => 'like'
            ],
            'group_name' => [
                'type' => 'in',
                'field' => 'group_name',
                'alias' => 'group_name',
                'filter' => 'like'
            ],
            'group_is_valid' => [
                'type' => 'in',
                'field' => 'is_valid',
                'alias' => 'group_is_valid',
                'filter' => 'where'
            ],
            'job_is_complete' => [
                'type' => 'in',
                'field' => 'is_complete',
                'alias' => 'job_is_complete',
                'filter' => 'where'
            ]
        ]);
    }

    private function getGroupFields()
    {
        return ([
            'group_id' => [
                'type' => 'in',
                'field' => 'group_id',
                'filter' => 'where',
                'alias' => 'group_id'
            ],
            'group_name' => [
                'type' => 'in',
                'field' => 'group_name',
                'filter' => 'none',
                'alias' => 'group_name'
            ],
            'start_date' => [
                'type' => 'in',
                'field' => 'start_date',
                'filter' => 'none',
                'alias' => 'start_date'
            ],
            'end_date' => [
                'type' => 'in',
                'field' => 'end_date',
                'filter' => 'none',
                'alias' => 'end_date'
            ],
            'click_date' => [
                'type' => 'in',
                'field' => 'click_date',
                'filter' => 'none',
                'alias' => 'click_date'
            ],
            'correction_date' => [
                'type' => 'in',
                'field' => 'correction_date',
                'filter' => 'none',
                'alias' => 'correction_date'
            ],
            'is_valid' => [
                'type' => 'in',
                'field' => 'is_valid',
                'filter' => 'none',
                'alias' => 'is_valid'
            ],
            'is_done' => [
                'type' => 'in',
                'field' => 'is_done',
                'filter' => 'none',
                'alias' => 'is_done'
            ],
            'is_complete' => [
                'type' => 'in',
                'field' => 'is_complete',
                'filter' => 'none',
                'alias' => 'is_complete'
            ],
            'comment' => [
                'type' => 'in',
                'field' => 'comment',
                'filter' => 'none',
                'alias' => 'comment'
            ],
            'corrector' => [
                'type' => 'in',
                'field' => 'corrector',
                'filter' => 'none',
                'alias' => 'corrector'
            ],
            'lead_email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['registration', 'lead_fk'], 'right' => ['student', 'id']]
                ],
                'field' => 'email',
                'filter' => 'none',
                'alias' => 'lead_email'
            ],
            'job_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']]
                ],
                'field' => 'id',
                'filter' => 'none',
                'alias' => 'job_id'
            ],
            'job_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['registration', 'job_fk'], 'right' => ['job', 'id']]
                ],
                'field' => 'name',
                'filter' => 'none',
                'alias' => 'job_name'
            ],
            'member' => [
                'type' => 'in',
                'field' => 'member_fk',
                'filter' => 'none',
                'alias' => 'member'
            ],
            'lead_github' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['registration', 'lead_fk'], 'right' => ['student', 'id']]
                ],
                'field' => 'github',
                'filter' => 'none',
                'alias' => 'lead_github'
            ],
            'lead_plesk' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['registration', 'lead_fk'], 'right' => ['student', 'id']]
                ],
                'field' => 'plesk',
                'filter' => 'none',
                'alias' => 'lead_plesk'
            ],
        ]);
    }
}
