<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Applicant_Model extends LPTF_Model
{
    private $table = 'applicant';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getApplicant($params)
    {
        $constraints = [
            ['applicant_id', 'optional', 'number', true], ['gender', 'optional', 'string'],
            ['firstname', 'optional', 'string'], ['lastname', 'optional', 'string'],
            ['birthdate', 'optional', 'string'], ['birthplace', 'optional', 'string'],
            ['email', 'optional', 'string'], ['phone', 'optional', 'string'],
            ['address', 'optional', 'string'], ['address_extension', 'optional', 'string'],
            ['postal_code', 'optional', 'number'], ['city', 'optional', 'string'],
            ['studies_level', 'optional', 'string'], ['studies', 'optional', 'string'],
            ['situation', 'optional', 'string'], ['beneficiary', 'optional', 'string'],
            ['qpv', 'optional', 'boolean'], ['handicap', 'optional', 'boolean'],
            ['source', 'optional', 'string'], ['creation_date', 'optional', 'string'],
            ['status', 'optional', 'string'], ['promotion_id', 'optional', 'number'],
            ['promotion_name', 'optional', 'string'], ['section_id', 'optional', 'number'],
            ['section_name', 'optional', 'string'], ['nir', 'optional', 'string'],
            ['student_id', 'optional', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getApplicantFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        return ($query->result_array());
    }

    public function postApplicant($params)
    {
        $constraints = [
            ['firstname', 'mandatory', 'string'], ['lastname', 'mandatory', 'string'],
            ['status', 'mandatory', 'string'], ['promotion_id', 'mandatory', 'number'],
            ['gender', 'optional', 'string'], ['birthdate', 'optional', 'string'],
            ['birthplace', 'optional', 'string'], ['email', 'optional', 'string'],
            ['phone', 'optional', 'string'], ['address', 'optional', 'string'],
            ['address_extension', 'optional', 'string'], ['postal_code', 'optional', 'number'],
            ['city', 'optional', 'string'], ['studies_level', 'optional', 'string'],
            ['studies', 'optional', 'string'], ['situation', 'optional', 'string'],
            ['beneficiary', 'optional', 'string'], ['qpv', 'optional', 'boolean'],
            ['handicap', 'optional', 'boolean'], ['source', 'optional', 'string'],
            ['nir', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        if (isset($params['nir']) && strlen($params['nir']) !== 15) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [
            'firstname' => $params['firstname'],
            'lastname' => $params['lastname'],
            'status' => $params['status'],
            'promotion_fk' => $params['promotion_id'],
            'creation_date' => date('Y-m-d')
        ];

        $optional_fields = [
            ['gender', 'gender'],
            ['birthdate', 'birthdate'],
            ['birthplace', 'birthplace'],
            ['email', 'email'],
            ['phone', 'phone'],
            ['address', 'address'],
            ['address_extension', 'address_extension'],
            ['postalcode', 'postal_code'],
            ['city', 'city'],
            ['studies_level', 'studies_level'],
            ['studies', 'studies'],
            ['situation', 'situation'],
            ['beneficiary', 'beneficiary'],
            ['qpv', 'qpv'],
            ['handicap', 'handicap'],
            ['source', 'source'],
            ['nir', 'nir']
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = null;
                if (strlen($params[$field[1]]) > 0) {
                    $data[$field[0]] = $params[$field[1]];
                }
            }
        }

        $this->db->trans_start();
        $response = $this->db->insert($this->table, $data);
        $insert_id = $this->db->insert_id();

        $this->load->model('Followup_Model');
        $data_followup = [
            'applicant_id' => (string)$insert_id,
            'comment' => "Creation de la fiche le " . date('Y-m-d')
        ];
        $this->Followup_Model->postFollowup($data_followup);

        $this->db->trans_complete();

        return ($response === true ? $insert_id : false);
    }

    public function putApplicant($params, $caller = null)
    {
        $constraints = [
            ['applicant_id', 'mandatory', 'number'],
            ['firstname', 'optional', 'string'], ['lastname', 'optional', 'string'],
            ['source', 'optional', 'string'], ['promotion_id', 'optional', 'number'],
            ['gender', 'optional', 'string'], ['birthdate', 'optional', 'string'],
            ['birthplace', 'optional', 'string'], ['email', 'optional', 'string'],
            ['phone', 'optional', 'string'], ['address', 'optional', 'string'],
            ['address_extension', 'optional', 'string'], ['postal_code', 'optional', 'number'],
            ['city', 'optional', 'string'], ['studies_level', 'optional', 'string'],
            ['studies', 'optional', 'string'], ['situation', 'optional', 'string'],
            ['beneficiary', 'optional', 'string'], ['qpv', 'optional', 'boolean'],
            ['handicap', 'optional', 'boolean'], ['status', 'optional', 'string'],
            ['nir', 'optional', 'string']
        ];

        // Si modification de la Promo étudiant dans Abandon : Pour forcer l'utilisateur a utiliser la route de Désinscription
        if($params['promotion_id'] == 49 && $caller == null) {
            return ($this->Status()->PreconditionFailed());
        }

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['firstname', 'firstname'],
            ['lastname', 'lastname'],
            ['status', 'status'],
            ['promotion_fk', 'promotion_id'],
            ['gender', 'gender'],
            ['birthdate', 'birthdate'],
            ['birthplace', 'birthplace'],
            ['email', 'email'],
            ['phone', 'phone'],
            ['address', 'address'],
            ['address_extension', 'address_extension'],
            ['postalcode', 'postal_code'],
            ['city', 'city'],
            ['studies_level', 'studies_level'],
            ['studies', 'studies'],
            ['situation', 'situation'],
            ['beneficiary', 'beneficiary'],
            ['qpv', 'qpv'],
            ['handicap', 'handicap'],
            ['source', 'source'],
            ['nir', 'nir']
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                if (strlen($params[$field[1]]) == 0) {
                    $data[$field[0]] = null;
                } else {
                    $data[$field[0]] = $params[$field[1]];
                }
            }
        }

        $this->db->trans_start();

        if (count($data) > 0) {
            if (isset($params['nir']) && strlen($params['nir']) !== 15) {
                return ($this->Status()->PreconditionFailed());
            }

            if (isset($params['promotion_id'])) {
                $applicant_params = [
                    'applicant_id' => $params['applicant_id'],
                    'promotion_id' => '',
                ];
                $applicant = $this->getApplicant($applicant_params);
                if (count($applicant) > 0 && $applicant[0]['promotion_id'] !== $params['promotion_id']) {
                    $this->load->model('Student_Model');
                    $data_student = [
                        'applicant_id' => $params['applicant_id'],
                    ];

                    $get_student = $this->Student_Model->getStudent($data_student);

                    if (count($get_student) == 1) {
                        $data_promotion_history = [
                            'promotion_id' => $params['promotion_id'],
                            'applicant_id' => $params['applicant_id']
                        ];

                        $this->load->model('Promotion_History_Model');
                        $response = $this->Promotion_History_Model->postPromotionHistory($data_promotion_history);
                    }
                }
            }

            $this->db->where('id', $params['applicant_id']);
            $response = $this->db->update($this->table, $data);
        } else {
            $response = $this->Status()->NoContent();
        }

        $this->db->trans_complete();

        return ($response);
    }

    public function deleteApplicant($params)
    {
        $constraints = [
            ['applicant_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->where_in('id', $params['applicant_id']);
        $response = $this->db->delete($this->table);

        return ($response);
    }

    public function getStatus($params)
    {
        $query = $this->db->query('SHOW COLUMNS FROM applicant LIKE \'status\'');
        $res = $query->result_array()[0]['Type'];
        $res = strstr($res, '(');
        $res = strstr($res, ')', true);
        $res = substr($res, 1);
        $res = explode(',', $res);

        $response = [];
        foreach ($res as $k => $e) {
            $row = [];
            $row['status_name'] = substr(substr($res[$k], 1), 0, -1);
            array_push($response, $row);
        }

        return ($response);
    }

    public function getSituations($params)
    {
        $query = $this->db->query('SHOW COLUMNS FROM applicant LIKE \'situation\'');
        $res = $query->result_array()[0]['Type'];
        $res = strstr($res, '(');
        $res = strstr($res, ')', true);
        $res = substr($res, 1);
        $res = explode(',', $res);

        $response = [];
        foreach ($res as $k => $e) {
            $row = [];
            $row['situation_name'] = substr(substr($res[$k], 1), 0, -1);
            array_push($response, $row);
        }

        return ($response);
    }

    public function getStudies($params)
    {
        $query = $this->db->query('SHOW COLUMNS FROM applicant LIKE \'studies_level\'');
        $res = $query->result_array()[0]['Type'];
        $res = strstr($res, '(');
        $res = strstr($res, ')', true);
        $res = substr($res, 1);
        $res = explode(',', $res);

        $response = [];
        foreach ($res as $k => $e) {
            $row = [];
            $row['study_name'] = substr(substr($res[$k], 1), 0, -1);
            array_push($response, $row);
        }

        return ($response);
    }

    public function postNewApplicant($params)
    {
        $this->load->model('Student_Model');
        $this->load->model('Promotion_Unit_Model');
        // $applicant = json_decode($params[]);
        // $all_applicants = json_decode($params['new_applicants']);

        $current_unit_id = $this->Promotion_Unit_Model->getPromotionUnit([
            'id' => '',
            'unit_id' => '',
            'promotion_id' => $params['promotion_id'],
            'order' => 'id',
            'desc' => '',
            'limit' => '1',
        ]);

        if (count($current_unit_id) == 0) {
            return ($this->Status()->ExpectationFailed());
        }

        $mandatory_applicant_fields = [
            'firstname', 'lastname', 'promotion_id',
        ];

        $this->db->trans_start();
        // foreach ($all_applicants as $applicant) {
        // if ($applicant->lptf_email == "error" || !str_contains($applicant->lptf_email, '@laplateforme.io')) {
        //         $applicant_data['success'] = false;
        //         $applicant_data['message'] = 'Email LPTF invalide';
        //         continue;
        //     }
            // if($applicant->personal_email == "error" || !str_contains($applicant->personal_email, '@')) {
            //     $applicant_data['success'] = false;
            //     $applicant_data['message'] = 'Email personnel invalide';
            //     continue;
            // }

            $missing_mandatory_field = false;
            $applicant_already_exists = false;
            $applicant_created_or_modified = false;
            $student_created = false;

            $applicant_data = [
                'firstname' => $params['firstname'],
                'lastname' => $params['lastname'],
                'email' => str_replace(' ', '', $params['personal_email']),
                'qpv' => isset($params['qpv']) ? $params['qpv'] : null,
                'handicap' => isset($params['handicap']) ? $params['handicap'] : null,
                'status' => isset($params['status']) ? $params['status'] : null,
                'promotion_id' => $params['promotion_id'],
            ];
            
            $applicant_insert_id = $this->postApplicant($applicant_data);

            if ($applicant_insert_id) {
                $student_data = [
                    'applicant_id' => (string)$applicant_insert_id,
                    'email' => str_replace(' ', '', $params['email']),
                    'current_unit_id' => $params['unit_id'],
                ];
                
                $student_insert_id = $this->Student_Model->postStudent($student_data);
                
                if ($student_insert_id) {
                    try {
                        $curl_duration = [];
                        array_push($curl_duration, new DateTime());
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, AUTH_URL . '/user/new_students?');
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                            'email' => $params['email'],
                            'role_id' => 1,
                            'student_id' => $student_insert_id,
                        ]));
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Token: ' . getallheaders()['Token'],
                        ));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $response = curl_exec($ch);
                        // $response = json_decode($response, true);
                        // var_dump($response);
                        if (!$response) {
                            curl_close($ch);
                            return ($this->Status()->ExpectationFailed());
                        }
                        curl_close($ch);
                    } catch (Exception $e) {
                        curl_close($ch);
                        return ($this->Status()->ExpectationFailed());
                    }
                }
            }

            else {
                return ($this->Status()->ExpectationFailed());
            }

        $this->db->trans_complete();
        return ($student_insert_id);
    }

    public function postNewApplicants($params)
    {
        // $constraints = [
        //     // ['new_applicants', 'mandatory', 'array'],
        //     ['promotion_id', 'mandatory', 'number']
        // ];
        // if ($this->api_helper->checkParameters($params, $constraints) == false) {
        //     return ($this->Status()->PreconditionFailed());
        // }
        $this->load->model('Student_Model');
        $this->load->model('Promotion_Unit_Model');
        $all_applicants = json_decode($params['new_applicants']);

        $current_unit_id = $this->Promotion_Unit_Model->getPromotionUnit([
            'id' => '',
            'unit_id' => '',
            'promotion_id' => $params['promotion_id'],
            'order' => 'id',
            'desc' => '',
            'limit' => '1',
        ]);

        if (count($current_unit_id) == 0) {
            return ($this->Status()->ExpectationFailed());
        }

        $response_array = [
            'recap' => [
                'total' => count($all_applicants),
                'applicant' => [
                    'missing_data' => 0,
                    'already_existing' => 0,
                    'new_entries' => 0,
                    'modifications' => 0,
                    'errors' => 0
                ],
                'student' => [
                    'missing_data' => 0,
                    'already_existing' => 0,
                    'new_entries' => 0,
                    'modifications' => 0,
                    'errors' => 0
                ],
                'user' => [
                    'missing_data' => 0,
                    'already_existing' => 0,
                    'new_entries' => 0,
                    'modifications' => 0,
                    'errors' => 0
                ],
                'total_errors' => 0,
                'total_new_entries' => 0,
                'total_modifications' => 0,
                'total_already_existing' => 0,
                'total_missing_data' => 0,
            ],
            'results' => [],
        ];

        $mandatory_applicant_fields = [
            'firstname', 'lastname', 'promotion_id',
        ];

        foreach ($all_applicants as $applicant) {

            $applicant_data = [
                'line' => $applicant->line,
                'gender' => $applicant->gender,
                'firstname' => $applicant->first_name,
                'lastname' => $applicant->last_name,
                'email' => str_replace(' ', '', $applicant->personal_email),
                'phone' => $applicant->phone,
                'birthdate' => isset($applicant->birthdate) ? date('Y-m-d', strtotime($applicant->birthdate)) : null,
                'birthplace' => isset($applicant->birthplace) ? $applicant->birthplace : null,
                'address' => isset($applicant->address1) ? $applicant->address1 : null,
                'address_extension' => isset($applicant->address_extension) ? $applicant->address_extension : null,
                'postalcode' => isset($applicant->postal_code) ? $applicant->postal_code : null,
                'city' => isset($applicant->city) ? $applicant->city : null,
                'studies_level' => isset($applicant->studies_level) ? $applicant->studies_level : null,
                'studies' => isset($applicant->studies) ? $applicant->studies : null,
                'situation' => isset($applicant->situation) ? $applicant->situation : null,
                'beneficiary' => isset($applicant->beneficiary) ? $applicant->beneficiary : null,
                'qpv' => isset($applicant->qpv) ? $applicant->qpv : null,
                'handicap' => isset($applicant->handicap) ? $applicant->handicap : null,
                'status' => isset($applicant->status) ? $applicant->status : null,
                'source' => isset($applicant->source) ? $applicant->source : null,
                'NIR' => isset($applicant->NIR) ? $applicant->NIR : null,
                'status' => "11",
                'creation_date' => date('Y-m-d'),
                'promotion_id' => $params['promotion_id'],
                'current_unit_id' => $current_unit_id[0]['unit_id'],
                'success' => true,
                'message' => '',
            ];

            if(!filter_var($applicant->lptf_email, FILTER_VALIDATE_EMAIL) || $applicant->lptf_email == "error") {
                $applicant_data['success'] = false;
                $applicant_data['message'] = 'Email LPTF invalide';
                array_push($response_array['results'], $applicant_data);
                continue;
            }

            if(!filter_var($applicant->personal_email, FILTER_VALIDATE_EMAIL) || $applicant->personal_email == "error") {
                $applicant_data['success'] = false;
                $applicant_data['message'] = 'Email personnel invalide';
                array_push($response_array['results'], $applicant_data);
                continue;
            }

            $missing_mandatory_field = false;
            $applicant_already_exists = false;
            $applicant_created_or_modified = false;
            $student_created = false;

            foreach ($mandatory_applicant_fields as $field) {
                if ($applicant_data[$field] == null) {
                    $applicant_data['success'] = false;
                    if ($applicant_data['message'] == '') $applicant_data['message'] = 'Champs manquants: ';
                    $applicant_data['message'] = $applicant_data['message'] . $field . ', ';
                    $missing_mandatory_field = true;
                }
            }

            if ($missing_mandatory_field) {
                $response_array['recap']['applicant']['missing_data']++;
                array_push($response_array['results'], $applicant_data);
                continue;
            }

            if (!$missing_mandatory_field) {
                $check_if_applicant_already_exists_params = [
                    'applicant_id' => '',
                    'email' => $applicant->personal_email,
                    'creation_date' => '',
                ];
                $check_if_applicant_already_exists = $this->getApplicant($check_if_applicant_already_exists_params);
                if (count($check_if_applicant_already_exists) > 0) {
                    $applicant_data['applicant_id'] = $check_if_applicant_already_exists[0]['applicant_id'];
                    $response_array['recap']['applicant']['already_existing']++;
                    $applicant_already_exists = true;
                }
            }

            if ($applicant_already_exists == false && $missing_mandatory_field == false) {
                $new_applicant = $this->postApplicant($applicant_data);
                if ($new_applicant) {
                    $applicant_data['applicant_id'] = $new_applicant;
                    $response_array['recap']['applicant']['new_entries']++;
                    $applicant_data['message'] = 'Applicant créé';
                    $applicant_created_or_modified = true;
                } else {
                    $response_array['recap']['applicant']['errors']++;
                    $applicant_data['message'] = 'Erreur lors de la création de l\'applicant';
                    $applicant_data['success'] = false;
                    array_push($response_array['results'], $applicant_data);
                    continue;
                }
            }

            if ($applicant_already_exists == true && $missing_mandatory_field == false) {
                $modified_applicant = $this->putApplicant($applicant_data);
                if ($modified_applicant) {
                    $response_array['recap']['applicant']['modifications']++;
                    $applicant_data['message'] = 'Applicant modifié';
                    $applicant_created_or_modified = true;
                } else {
                    $response_array['recap']['applicant']['errors']++;
                    $applicant_data['success'] = false;
                    $applicant_data['message'] = 'Erreur lors de la modification de l\'applicant';
                    array_push($response_array['results'], $applicant_data);
                    continue;
                }
            }

            if ($applicant_created_or_modified) {
                $check_if_student_already_exists = $this->Student_Model->getStudent([
                    'student_id' => '',
                    'email' => str_replace(' ', '', $applicant->lptf_email),
                ]);
                if (count($check_if_student_already_exists) > 0) {
                    $response_array['recap']['student']['already_existing']++;
                    $modified_student = $this->Student_Model->putStudent([
                        'student_id' => (string)$check_if_student_already_exists[0]['student_id'],
                        'current_unit_id' => $applicant_data['current_unit_id'],
                    ]);
                    if ($modified_student) {
                        $applicant_data['message'] = $applicant_data['message'] . ', student modifié';
                        array_push($response_array['results'], $applicant_data);
                        $response_array['recap']['student']['modifications']++;
                    } else {
                        $applicant_data['message'] = $applicant_data['message'] . ', erreur lors de la modification du student';
                        $applicant_data['success'] = false;
                        array_push($response_array['results'], $applicant_data);
                        continue;
                    }
                } else {
                    $student_data = [
                        'applicant_id' => (string)$applicant_data['applicant_id'],
                        'email' => str_replace(' ', '', $applicant->lptf_email),
                        'current_unit_id' => $applicant_data['current_unit_id'],
                    ];
                    $new_student = $this->Student_Model->postStudent($student_data);
                    if ($new_student) {
                        $response_array['recap']['student']['new_entries']++;
                        $applicant_data['message'] = $applicant_data['message'] . ', student créé';
                        $student_created = true;
                    } else {
                        $response_array['recap']['student']['errors']++;
                        $applicant_data['message'] = $applicant_data['message'] . ', erreur lors de la création du student';
                        $applicant_data['success'] = false;
                        array_push($response_array['results'], $applicant_data);
                        continue;
                    }
                }
            }

            if ($student_created === true) {
                try {
                    $curl_duration = [];
                    array_push($curl_duration, new DateTime());
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, AUTH_URL . '/user/new_students?');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                        'email' => $applicant->lptf_email,
                        'role_id' => 1,
                        'student_id' => $new_student,
                    ]));
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Token: ' . getallheaders()['Token'],
                    ));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    $response = json_decode($response, true);
                    if ($response) {
                        $applicant_data['success'] = true;
                        $applicant_data['message'] = $applicant_data['message'] . ', utilisateur/scope créé';
                        $response_array['recap']['user']['new_entries']++;
                        array_push($response_array['results'], $applicant_data);
                    } else {
                        $applicant_data['success'] = false;
                        $applicant_data['message'] = $applicant_data['message'] . ', erreur lors de la création de l\'utilisateur/scope';
                        $response_array['recap']['user']['errors']++;
                        array_push($response_array['results'], $applicant_data);
                    }
                    curl_close($ch);
                } catch (Exception $e) {
                    $applicant_data['success'] = false;
                    $applicant_data['message'] = $applicant_data['message'] . ', erreur lors de la création de l\'utilisateur/scope';
                    array_push($response_array['results'], $applicant_data);
                    $response_array['recap']['user']['errors']++;
                    curl_close($ch);
                }
            }
        }
            $response_array['recap']['total_errors'] = $response_array['recap']['applicant']['errors'] + $response_array['recap']['student']['errors'] + $response_array['recap']['user']['errors'];
            $response_array['recap']['total_new_entries'] = $response_array['recap']['applicant']['new_entries'] + $response_array['recap']['student']['new_entries'] + $response_array['recap']['user']['new_entries'];
            $response_array['recap']['total_modifications'] = $response_array['recap']['applicant']['modifications'] + $response_array['recap']['student']['modifications'] + $response_array['recap']['user']['modifications'];
            $response_array['recap']['total_already_existing'] = $response_array['recap']['applicant']['already_existing'] + $response_array['recap']['student']['already_existing'] + $response_array['recap']['user']['already_existing'];
            $response_array['recap']['total_missing_data'] = $response_array['recap']['applicant']['missing_data'] + $response_array['recap']['student']['missing_data'] + $response_array['recap']['user']['missing_data'];

        return ($response_array);
    }

    private function getApplicantFields()
    {
        return ([
            'applicant_id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'gender' => [
                'type' => 'in',
                'field' => 'gender',
                'filter' => 'where'
            ],
            'firstname' => [
                'type' => 'in',
                'field' => 'firstname',
                'filter' => 'like'
            ],
            'lastname' => [
                'type' => 'in',
                'field' => 'lastname',
                'filter' => 'like'
            ],
            'birthdate' => [
                'type' => 'in',
                'field' => 'birthdate',
                'filter' => 'none'
            ],
            'birthplace' => [
                'type' => 'in',
                'field' => 'birthplace',
                'filter' => 'none'
            ],
            'email' => [
                'type' => 'in',
                'field' => 'email',
                'filter' => 'like'
            ],
            'phone' => [
                'type' => 'in',
                'field' => 'phone',
                'filter' => 'none'
            ],
            'address' => [
                'type' => 'in',
                'field' => 'address',
                'filter' => 'none'
            ],
            'address_extension' => [
                'type' => 'in',
                'field' => 'address_extension',
                'filter' => 'none'
            ],
            'postal_code' => [
                'type' => 'in',
                'field' => 'postalcode',
                'alias' => 'applicant_postal_code',
                'filter' => 'where'
            ],
            'city' => [
                'type' => 'in',
                'field' => 'city',
                'filter' => 'like'
            ],
            'studies_level' => [
                'type' => 'in',
                'field' => 'studies_level',
                'filter' => 'where'
            ],
            'studies' => [
                'type' => 'in',
                'field' => 'studies',
                'filter' => 'none'
            ],
            'situation' => [
                'type' => 'in',
                'field' => 'situation',
                'filter' => 'where'
            ],
            'beneficiary' => [
                'type' => 'in',
                'field' => 'beneficiary',
                'filter' => 'none'
            ],
            'qpv' => [
                'type' => 'in',
                'field' => 'qpv',
                'filter' => 'where'
            ],
            'handicap' => [
                'type' => 'in',
                'field' => 'handicap',
                'filter' => 'where'
            ],
            'status' => [
                'type' => 'in',
                'field' => 'status',
                'filter' => 'where'
            ],
            'source' => [
                'type' => 'in',
                'field' => 'source',
                'filter' => 'none'
            ],
            'creation_date' => [
                'type' => 'in',
                'field' => 'creation_date',
                'filter' => 'none'
            ],
            'promotion_id' => [
                'type' => 'in',
                'field' => 'promotion_fk',
                'alias' => 'promotion_id',
                'filter' => 'where'
            ],
            'promotion_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'name',
                'alias' => 'promotion_name',
                'filter' => 'like',
            ],
            'section_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'section_fk',
                'alias' => 'section_id',
                'filter' => 'where'
            ],
            'section_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']],
                    ['left' => ['promotion', 'section_fk'], 'right' => ['section', 'id']]
                ],
                'field' => 'name',
                'alias' => 'section_name',
                'filter' => 'like'
            ],
            'nir' => [
                'type' => 'in',
                'field' => 'nir',
                'filter' => 'where'
            ],
            'student_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['applicant', 'id'], 'right' => ['student', 'applicant_fk']],
                ],
                'field' => 'id',
                'alias' => 'student_id',
                'filter' => 'where'
            ]
        ]);
    }
}
