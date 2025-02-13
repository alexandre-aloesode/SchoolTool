<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Student_Model extends LPTF_Model
{
    private $table = 'student';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getStudent($params)
    {
        $constraints = [
            ['student_id', 'optional', 'number'],
            ['applicant_id', 'optional', 'number'],
            ['firstname', 'optional', 'string'],
            ['lastname', 'optional', 'string'],
            ['email', 'optional', 'string'],
            ['current_unit_id', 'optional', 'number', true],
            ['current_unit_name', 'optional', 'string'],
            ['current_unit_code', 'optional', 'string'],
            ['section_id', 'optional', 'number'],
            ['section_name', 'optional', 'string'],
            ['promotion_id', 'optional', 'number', true],
            ['promotion_name', 'optional', 'string'],
            ['promotion_is_active', 'optional', 'string'],
            ['student_unit', 'optional', 'number', true],
            ['github', 'optional', 'string'],
            ['linkedin', 'optional', 'string'],
            ['cv', 'optional', 'string'],
            ['plesk', 'optional', 'string'],
            ['personal_website', 'optional', 'string'],
            ['calendar_id', 'optional', 'number'],
            ['promotion_start_date', 'optional', 'string'],
            ['promotion_end_date', 'optional', 'string'],
            ['promotion_duration', 'optional', 'number'],
            ['birthdate', 'optional', 'string'],
            ['birthplace', 'optional', 'string'],
            ['badge', 'optional', 'string', true],
            ['personal_email', 'optional', 'string'],
        ];

        $getStudentAlerts = false;

        if (isset($params['alerts'])) {
            $getStudentAlerts = true;
            unset($params['alerts']);
        }

        if (isset($params['student_unit']) && !isset($params['student_id'])) {
            $params['student_id'] = '';
        }

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getStudentFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        $students_arr = $query->result_array();

        if (isset($params['student_unit'])) {
            $formated_arr = [];
            foreach ($students_arr as $student_entry) {
                $stop = false;
                foreach ($formated_arr as $key => $student) {
                    if ($student['student_id'] === $student_entry['student_id']) {
                        array_push($formated_arr[$key]['unit_id'], $student_entry['unit_id']);
                        $stop = true;
                        break;
                    }
                }

                if (!$stop) {
                    $student_entry['unit_id'] = [$student_entry['unit_id']];
                    array_push($formated_arr, $student_entry);
                }
            }

            $students_arr = $formated_arr;
        }

        if ($getStudentAlerts == true) {
            $this->load->model('Alert_Model');
            foreach ($students_arr as $key => $student) {
                $students_arr[$key]['alerts'] = $this->Alert_Model->getAlert([
                    'student_id' => $student['student_id'],
                    'level' => '',
                    'date' => '',
                    'order' => 'date',
                    'desc' => '',
                ]);
            }
        }

        return ($students_arr);
    }

    public function postStudent($params)
    {
        $constraints = [
            ['email', 'mandatory', 'string'],
            ['current_unit_id', 'mandatory', 'number'],
            ['applicant_id', 'mandatory', 'number'],
            ['github', 'optional', 'string'],
            ['linkedin', 'optional', 'string'],
            ['cv', 'optional', 'string'],
            ['plesk', 'optional', 'string'],
            ['personal_website', 'optional', 'string'],
            ['calendar_id', 'optional', 'number'],
            ['badge', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->trans_start();

        $data = [
            'email' => $params['email'],
            'applicant_fk' => $params['applicant_id'],
            'current_unit_fk' => $params['current_unit_id']
        ];

        $optional_fields = [
            ['github', 'github'],
            ['linkedin', 'linkedin'],
            ['cv', 'cv'],
            ['plesk', 'plesk'],
            ['personal_website', 'personal_website'],
            ['calendar_id', 'calendar_id'],
            ['badge', 'badge']
        ];

        if (array_key_exists('linkedin', $params) && !empty($params['linkedin'])) {
            $params['linkedin'] = "https://www.linkedin.com/in/" . $params['linkedin'];
        }

        if (array_key_exists('github', $params) && !empty($params['github'])) {
            $params['github'] = "https://github.com/" . $params['github'];
        }

        if (array_key_exists('calendar_id', $params) && !empty($params['calendar_id'])) {
            $params['calendar_id'] = $params['calendar_id'];
        }

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }
        $response = $this->db->insert($this->table, $data);
        $inserted_id = $this->db->insert_id();

        $this->load->model('Applicant_Model');
        $data_applicant = [
            'applicant_id' => $params['applicant_id'],
            'promotion_id' => ''
        ];

        $get_applicant = $this->Applicant_Model->getApplicant($data_applicant);

        $data_promotion_history = [
            'promotion_id' => $get_applicant[0]['promotion_id'],
            'applicant_id' => $params['applicant_id']
        ];

        $this->load->model('Promotion_History_Model');
        $this->Promotion_History_Model->postPromotionHistory($data_promotion_history);

        $this->db->trans_complete();

        return ($response === true ? $inserted_id : false);
    }

    public function putStudent($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number'],
            ['firstname', 'optional', 'string'],
            ['lastname', 'optional', 'string'],
            ['student_email', 'optional', 'string'],
            ['current_unit_id', 'optional', 'number'],
            ['promotion_id', 'optional', 'number'],
            ['github', 'optional', 'string'],
            ['linkedin', 'optional', 'string'],
            ['cv', 'optional', 'string'],
            ['plesk', 'optional', 'string'],
            ['personal_website', 'optional', 'string'],
            ['calendar_id', 'optional', 'number'],
            ['badge', 'optional', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['email', 'student_email'],
            ['current_unit_fk', 'current_unit_id'],
            ['github', 'github'],
            ['linkedin', 'linkedin'],
            ['cv', 'cv'],
            ['plesk', 'plesk'],
            ['personal_website', 'personal_website'],
            ['calendar_fk', 'calendar_id'],
            ['badge', 'badge']
        ];

        if (array_key_exists('linkedin', $params) && !empty($params['linkedin'])) {
            $params['linkedin'] = "https://www.linkedin.com/in/" . $params['linkedin'];
        }

        if (array_key_exists('github', $params) && !empty($params['github'])) {
            $params['github'] = "https://github.com/" . $params['github'];
        }

        if (array_key_exists('calendar_id', $params) && !empty($params['calendar_id'])) {
            $params['calendar_id'] = $params['calendar_id'];
        }

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = null;
                if (strlen($params[$field[1]]) > 0) {
                    $data[$field[0]] = $params[$field[1]];
                }
            }
        }

        $data_applicant = [];
        $optional_fields_applicant = [
            ['promotion_id', 'promotion_id'],
            ['firstname', 'firstname'],
            ['lastname', 'lastname']
        ];

        foreach ($optional_fields_applicant as $field) {
            if (array_key_exists($field[1], $params)) {
                $data_applicant[$field[0]] = $params[$field[1]];
            }
        }

        $this->db->trans_start();
        $update_count = 0;
        if (count($data) > 0) {
            $this->db->where('id', $params['student_id']);
            $response = $this->db->update($this->table, $data);
            ++$update_count;
        }

        if (count($data_applicant) > 0) {
            $student_params = [
                'student_id' => $params['student_id'],
                'applicant_id' => '',
            ];
            $student = $this->getStudent($student_params);
            if (count($student) == 1) {
                $data_applicant['applicant_id'] = $student[0]['applicant_id'];
                $this->load->model('Applicant_Model');
                $response = $this->Applicant_Model->putApplicant($data_applicant);
                ++$update_count;
            }
        }

        $this->db->trans_complete();

        if ($update_count <= 0) {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteStudent($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number', true]
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->where_in('id', $params['student_id']);
        $response = $this->db->delete($this->table);

        return ($response);
    }

    public function deleteStudentActivity($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number', true]
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->load->model('Registration_Model');
        $registration_params = [
            'member_id' => $params['student_id'],
            'group_id' => '',
            'job_is_complete' => '0',
        ];
        $student_registrations = $this->Registration_Model->getRegistration($registration_params);

        $this->db->trans_start();

        foreach ($student_registrations as $registration) {
            $data = [
                'group_id' => $registration['group_id'],
                'student_id' => $params['student_id'],
            ];

            $this->Registration_Model->deleteMember($data);
        }

        $this->load->model('Unit_Viewer_Model');
        $student_unit_params = [
            'student_id' => $params['student_id'],
            'unit_id' => '',
        ];
        $student_units = $this->Unit_Viewer_Model->getStudentUnit($student_unit_params);

        foreach ($student_units as $unit) {
            $data = [
                'student_id' => $params['student_id'],
                'unit_id' => $unit['unit_id'],
            ];

            $this->Unit_Viewer_Model->deleteUnitStudent($data);
        }

        $this->load->model('Waiting_List_Model');
        $waiting_list_params = [
            'student_id' => $params['student_id'],
        ];
        $waiting_lists = $this->Waiting_List_Model->deleteWaitingList($waiting_list_params);

        $this->load->model('Alert_Model');
        $alert_params = [
            'alert_id' => '',
            'student_id' => $params['student_id'],
            'status' => '',
        ];
        $student_alerts = $this->Alert_Model->getAlert($alert_params);

        $this->load->model('Applicant_Model');
        $applicant_data = $this->Applicant_Model->getApplicant([
            'applicant_id' => '',
            'student_id' => $params['student_id'],
        ]);

        $this->Applicant_Model->putApplicant([
            'applicant_id' => $applicant_data[0]['applicant_id'],
            'promotion_id' => '49'
        ], 'api');

        if (count($student_alerts) > 0) {
            foreach ($student_alerts as $key => $alert) {
                if ($student_alerts[$key]['alert_status'] != '1') {
                    $update_alert_params = [
                        'alert_id' => $student_alerts[$key]['alert_id'],
                        'status' => "1",
                    ];
                    $this->Alert_Model->putAlert($update_alert_params);
                }
            }
        }

        $student_params = [
            'student_id' => $params['student_id'],
            'applicant_id' => '',
        ];
        $student = $this->getStudent($student_params);

        $comment = 'Étudiant désinscrit le ' . date("d-m-Y");
        $this->load->model('Followup_Model');
        $followup_params = [
            'applicant_id' => $student[0]['applicant_id'],
            'comment' => $comment,
            'type' => 'adm',
        ];
        $this->Followup_Model->postFollowup($followup_params);

        $this->db->trans_complete();

        return (true);
    }

    public function getStudentInactive($params)
    {
        $constraints = [
            ['promotion_id', 'optional', 'number']
        ];

        // if ($this->api_helper->checkParameters($params, $constraints) == false)
        // { return ($this->Status()->PreconditionFailed()); }

        $this->db->trans_start();

        $student_params = [
            'student_id' => '',
            'firstname' => '',
            'lastname' => '',
        ];

        if (isset($params['promotion_id'])) {
            $student_params['promotion_id'] = $params['promotion_id'];
        }

        $students_arr = $this->getStudent($student_params);

        $inactive_students = [];

        foreach ($students_arr as $student) {
            $inactive = false;

            $student_jobs_in_progress_params = [
                'member_id' => $student['student_id'],
                'click_date' => 'null',
                'group_is_valid' => '1',
                'end_date' => '',
            ];

            // On récup les jobs en cours de l'étudiant
            $this->load->model('Registration_Model');
            $student_jobs_in_progress = $this->Registration_Model->getRegistration($student_jobs_in_progress_params);

            // L'étudiant n'a pas de job en cours
            if (count($student_jobs_in_progress) === 0) {
                // Check de la date du dernier rendu
                $student_last_job_done_params = [
                    'member_id' => $student['student_id'],
                    // 'job_is_complete' => '1',
                    'click_date' => '',
                    'order' => 'click_date',
                    'desc' => '',
                    'limit' => '1',
                ];
                $student_last_job_done = $this->Registration_Model->getRegistration($student_last_job_done_params);

                if (count($student_last_job_done) > 0) {
                    $now = date_create(date('Y-m-d H:i:s'));
                    $last_job_done_date = date_create($student_last_job_done[0]['click_date']);
                    $interval_in_days = date_diff($now, $last_job_done_date)->format('%d');

                    // Si son dernier projet rendu date de plus de 7j
                    if ($interval_in_days > 7) {
                        $inactive = true;
                    }
                }
            } else {
                $last_job_in_progress = end($student_jobs_in_progress);
                $now = date_create(date('Y-m-d H:i:s'));
                $last_job_in_progress_end_date = date_create($last_job_in_progress['end_date']);
                $interval_in_days = date_diff($now, $last_job_in_progress_end_date)->format('%d');
                $is_positive_interval = $now < $last_job_in_progress_end_date;

                // Retard de plus de 5 jours sur le dernier projets en cours
                if ($interval_in_days > 5 && !$is_positive_interval) {
                    // Check de la date du dernier rendu
                    $student_last_job_done_params = [
                        'member_id' => $student['student_id'],
                        // 'job_is_complete' => '1',
                        'click_date' => '',
                        'order' => 'click_date',
                        'desc' => '',
                        'limit' => '1',
                    ];
                    $student_last_job_done = $this->Registration_Model->getRegistration($student_last_job_done_params);

                    if (count($student_last_job_done) > 0) {
                        $now = date_create(date('Y-m-d H:i:s'));
                        $last_job_done_date = date_create($student_last_job_done[0]['click_date']);
                        $interval_in_days = date_diff($now, $last_job_done_date)->format('%d');

                        // Si son dernier projet rendu date de plus de 7j
                        if ($interval_in_days > 7) {
                            $inactive = true;
                        }
                    }
                }
            }

            if ($inactive) {
                $student['inactivity'] = $interval_in_days;
                array_push($inactive_students, $student);
            }
        }

        $this->db->trans_complete();

        return ($inactive_students);
    }

    public function getStudentFaithfulness($params)
    {
        $constraints = [
            ['applicant_id', 'mandatory', 'number'],
            ['promotion_id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->load->model('Applicant_Model');
        $this->load->model('Promotion_History_Model');

        $actual_promotion_params = [
            'applicant_id' => $params['applicant_id'],
            'promotion_id' => $params['promotion_id'],
            'date' => '',
        ];
        $applicant_actual_promotion = $this->Promotion_History_Model->getPromotionHistory($actual_promotion_params);
        if (count($applicant_actual_promotion) == 0) {
            return false;
        }
        $applicant_actual_promotion[0]['promotion_history_date'] = date('Y-m-d', strtotime($applicant_actual_promotion[0]['promotion_history_date'] . ' +1 day'));

        $promotion_history_params = [
            'applicant_id' => $params['applicant_id'],
            'promotion_id' => '',
            'date' => '',
            'date_after' => $applicant_actual_promotion[0]['promotion_history_date'],
            'order' => 'date',
            'asc' => '',
        ];
        $promotion_history = $this->Promotion_History_Model->getPromotionHistory($promotion_history_params);
        if (count($promotion_history) == 0) {
            return true;
        } else {
            if (in_array($promotion_history[0]['promotion_id'], TAB_ABANDONS)) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function getStudentAttendance($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number', true],
            ['start_date', 'mandatory', 'string'],
            ['end_date', 'mandatory', 'string'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $student = [
            'student_id' => $params['student_id'],
            'school_days_found' => 0,
            'company_days_found' => 0,
            'attendance' => [],
            'total_hours_to_do' => 0,
            'total_hours_done' => 0,
            'real_total_hours_done' => 0,
            'absences' => [],
            'total_absences_days' => 0,
            'message' => '',
        ];

        $this->load->model('Calendar_Day_Model');

        $student_calendar_id = $this->getStudent([
            'student_id' => $params['student_id'],
            'calendar_id' => ''
        ]);

        if ($student_calendar_id == null || $student_calendar_id == "") {
            $week['response'] = false;
            $week['message'] = "L'étudiant n'a pas de calendrier associé";
            return ($student);
        }

        $this->load->model('Logtime_Model');
        $this->load->model('Absence_Model');

        $start_date = new DateTime($params['start_date']);
        $end_date = new DateTime($params['end_date']);

        while ($start_date <= $end_date) {

            $day_to_add = [
                'date' => $start_date->format('Y-m-d'),
                'day_in_number' => $start_date->format('w'),
                'type' => '',
                'logtime' => 0,
            ];

            $day_check = $this->Calendar_Day_Model->getCalendarDay([
                'calendar_id' => $student_calendar_id[0]['student_calendar_id'],
                'day' => $start_date->format('Y-m-d'),
                'type' => '',
            ]);

            if (count($day_check) > 0) {
                $day_to_add['type'] = $day_check[0]['calendar_day_type'];
            } else {
                if ($start_date->format('w') == 06 || $start_date->format('w') == 0) $day_to_add['type'] = 'week_end';
                else $day_to_add['type'] = 'holiday';
            }

            array_push($student['attendance'], $day_to_add);

            $start_date->modify('+1 day');
        }

        $potential_absences = $this->Absence_Model->getAbsence([
            'id' => '',
            'student_id' => $params['student_id'],
            'status' => '1',
            'start_date' => '',
            'end_date' => '',
            'reason' => '',
            'duration' => '',
        ]);

        $absences_already_pushed = [];

        $weekly_logtime = 0;
        $weekly_school_days = 0;

        foreach ($student['attendance'] as $key => $day) {

            if ($day['type'] == 'holiday') continue;

            elseif ($day['type'] == '2') {
                $student['company_days_found'] += 1;
                continue;
            } elseif ($day['type'] == '1') {
                $student['school_days_found'] += 1;
                $student['total_hours_to_do'] += 7;
                $weekly_school_days++;

                $logtime = $this->Logtime_Model->getLogtime([
                    'student_id' => $params['student_id'],
                    'day' => $day['date'],
                    'algo2' => '',
                ]);

                if (count($logtime) > 0) {
                    $student['attendance'][$key]['logtime'] += $logtime[0]['logtime_algo2'] / 60;
                    $weekly_logtime += $logtime[0]['logtime_algo2'] / 60;
                }

                if (count($potential_absences) > 0) {
                    foreach ($potential_absences as $key => $absence) {
                        $absence_start_date = new DateTime($absence['absence_start_date']);
                        $absence_end_date = new DateTime($absence['absence_end_date']);
                        $school_day =  new DateTime($day['date']);
                        if ($school_day >= $absence_start_date && $school_day < $absence_end_date) {
                            $student['total_absences_days'] += 1;
                            if (!in_array($absence['absence_id'], $absences_already_pushed)) {
                                array_push($absences_already_pushed, $absence['absence_id']);
                                array_push($student['absences'], $absence);
                            }
                        }
                    }
                }
            } elseif ($day['type'] == 'week_end') {
                
                //Si la date de début sélectionnée est un samedi ou un dimanche on saute ces jours
                if ($key == 0 || $key == 1) continue;

                //Potentiellement on aura des promotions ayant des semaines avec des jours de cours ET en entreprise. On part du principe
                //que si l'on trouve un jour de cours dans la semaine en question, on peut rajouter les temps de log du weekend
                $school_day_found = false;

                //On fait une boucle qui remonte de 6 jours en arrière si on est le dimanche, pour arriver jusqu'au lundi
                //de la semaine et check si on a trouvé un jour de cours
                for ($number_days_to_substract = 1; $number_days_to_substract < 7; $number_days_to_substract++) {

                    if (!isset($student['attendance'][$key - $number_days_to_substract])) break;
                    
                    if ($student['attendance'][$key - $number_days_to_substract]['type'] == '1') {
                        $school_day_found = true;
                        break;
                    }
                    //Si on est un samedi on arrête la boucle à 5 jours
                    if ($day['day_in_number'] == 6 && $number_days_to_substract == 5) break;
                }

                if ($school_day_found == true) {
                    $week_end_logtime = $this->Logtime_Model->getLogtime([
                        'student_id' => $params['student_id'],
                        'day' => $day['date'],
                        'algo2' => '',
                    ]);

                    if (count($week_end_logtime) > 0) {
                        $student['attendance'][$key]['logtime'] += $week_end_logtime[0]['logtime_algo2'] / 60;
                        $weekly_logtime += $week_end_logtime[0]['logtime_algo2'] / 60;
                    }
                }
            }

            if ($day['day_in_number'] == 0 || $key == (count($student['attendance']) - 1)) {
                $student['real_total_hours_done'] += $weekly_logtime;
                if ($weekly_logtime > ($weekly_school_days * 7)) $weekly_logtime = ($weekly_school_days * 7);
                $student['total_hours_done'] += $weekly_logtime;
                $weekly_logtime = 0;
                $weekly_school_days = 0;
            }
        }

        if ($student['school_days_found'] == 0) $student['message'] = "Aucun jour de cours trouvé sur cette période";

        return ($student);
    }

    public function getGroupAttendance($params)
    {
        $constraints = [
            ['student_id', 'mandatory', 'number', true],
            ['start_date', 'mandatory', 'string'],
            ['end_date', 'mandatory', 'string']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        if (!is_array($params['student_id'])) {
            return $this->getStudentAttendance([  
                'student_id' => $params['student_id'],
                'start_date' => $params['start_date'],
                'end_date' => $params['end_date']
            ]);
        }

        $students_array = [];

        foreach ($params['student_id'] as $student_id) {
            array_push($students_array, $this->getStudentAttendance([
                'student_id' => $student_id,
                'start_date' => $params['start_date'],
                'end_date' => $params['end_date']
            ]));
        }

        return ($students_array);
    }

    public function postNewBadges($params)
    {
        $all_students = json_decode($params['new_badges']);

        $response_array = [
            'recap' => [
                'total' => count($all_students),
                'missing_data' => 0,
                'modifications' => 0,
                'errors' => 0
            ],
            'results' => [],
        ];

        $mandatory_student_fields = [
            'firstname',
            'lastname',
            'badge',
        ];

        // $this->db->trans_start();
        foreach ($all_students as $student) {

            $missing_mandatory_field = false;

            $student_data = [
                'line' => $student->line,
                'firstname' => $student->first_name,
                'lastname' => $student->last_name,
                'email' => str_replace(' ', '', $student->lptf_email),
                'badge' => $student->badge,
                'message' => '',
                'success' => true,
            ];

            foreach ($mandatory_student_fields as $field) {
                if ($student_data[$field] == null) {
                    $student_data['success'] = false;
                    if ($student_data['message'] == '') $student_data['message'] = 'Champs manquants: ';
                    $student_data['message'] = $student_data['message'] . $field . ', ';
                    $missing_mandatory_field = true;
                }
            }

            if ($missing_mandatory_field) {
                $response_array['recap']['missing_data']++;
                array_push($response_array['results'], $student_data);
                continue;
            }

            if (!$missing_mandatory_field) {
                $student_request_params = [
                    'student_id' => '',
                    // 'email' => $student->lptf_email,
                    'firstname' => $student->first_name,
                    'lastname' => $student->last_name,
                    'badge' => '',
                ];
                $student_database_info = $this->getStudent($student_request_params);
                if (count($student_database_info)  == 0) {
                    $student_data['success'] = false;
                    $student_data['message'] = 'Etudiant non trouvé';
                    $response_array['recap']['errors']++;
                    array_push($response_array['results'], $student_data);
                    continue;
                } else {
                    $badge_update = $this->putStudent([
                        'student_id' => $student_database_info[0]['student_id'],
                        'badge' => $student->badge,
                    ]);
                    if ($badge_update == false) {
                        $student_data['success'] = false;
                        $student_data['message'] = 'Erreur lors de la mise à jour';
                        $response_array['recap']['errors']++;
                        array_push($response_array['results'], $student_data);
                        continue;
                    } else {
                        $student_data['success'] = true;
                        $student_data['message'] = 'Badge mis à jour';
                        $response_array['recap']['modifications']++;
                        array_push($response_array['results'], $student_data);
                    }
                }
            }
        }
        return ($response_array);
    }

    public function getLastLog($params)
    {

        $constraints = [
            ['student_id', 'mandatory', 'number'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $student_data = $this->getStudent([
            'student_id' => $params['student_id'],
            'email' => ''
        ]);
        if (count($student_data) == 0) return ($this->Status()->NoContent());

        try {
            $db = new PDO('mysql:host=' . DB_HOST_LOGTIME . ';dbname=' . DB_NAME_LOGTIME, DB_USER_LOGTIME, DB_PWD_LOGTIME, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $get_last_ping_stmt = "SELECT id, username, date AS logtime_day FROM Connected WHERE username = :username ORDER BY date DESC LIMIT 1";
            $get_last_ping = $db->prepare($get_last_ping_stmt);
            $get_last_ping->execute([
                'username' => $student_data[0]['student_email']
            ]);
            $get_last_ping_result = $get_last_ping->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {

            $this->load->model('Logtime_Model');
            return $this->Logtime_Model->getStudentLastLogtime([
                'student_id' => $params['student_id'],
                'logtime_id' =>  "",
                'username' =>  $student_data[0]['student_email'],
                'day' =>  "",
                'algo1' =>  "",
                'algo2' =>  "",
                'algo3' =>  "",
            ]);
        }

        if (!$get_last_ping_result || empty($get_last_ping_result)) {
            $this->load->model('Logtime_Model');
            return $this->Logtime_Model->getStudentLastLogtime([
                'student_id' => $params['student_id'],
                'logtime_id' =>  "",
                'username' =>  $student_data[0]['student_email'],
                'day' =>  "",
                'algo1' =>  "",
                'algo2' =>  "",
                'algo3' =>  "",
            ]);
        }

        return $get_last_ping_result;
    }

    private function getStudentFields()
    {
        return ([
            'student_id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'applicant_id' => [
                'type' => 'in',
                'field' => 'applicant_fk',
                'alias' => 'applicant_id',
                'filter' => 'where'
            ],
            'firstname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'alias' => 'student_firstname',
                'field' => 'firstname',
                'filter' => 'like',
            ],
            'lastname' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'alias' => 'student_lastname',
                'field' => 'lastname',
                'filter' => 'like',
            ],
            'email' => [
                'type' => 'in',
                'field' => 'email',
                'alias' => 'student_email',
                'filter' => 'like'
            ],
            'current_unit_id' => [
                'type' => 'in',
                'field' => 'current_unit_fk',
                'alias' => 'current_unit_id',
                'filter' => 'where',
            ],
            'current_unit_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'current_unit_fk'], 'right' => ['unit', 'id']]
                ],
                'field' => 'name',
                'alias' => 'current_unit_name',
                'filter' => 'where',
            ],
            'current_unit_code' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'current_unit_fk'], 'right' => ['unit', 'id']]
                ],
                'field' => 'code',
                'alias' => 'current_unit_code',
                'filter' => 'where',
            ],
            'section_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'section_fk',
                'alias' => 'section_id',
                'filter' => 'where',
            ],
            'section_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']],
                    ['left' => ['promotion', 'section_fk'], 'right' => ['section', 'id']]
                ],
                'field' => 'name',
                'alias' => 'section_name',
                'filter' => 'where',
            ],
            'promotion_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' => 'promotion_fk',
                'alias' => 'promotion_id',
                'filter' => 'where',
            ],
            'promotion_name' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'name',
                'filter' => 'where',
            ],
            'promotion_is_active' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'is_active',
                'filter' => 'where',
            ],
            'student_unit' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'id'], 'right' => ['unit_viewer', 'student_fk']]
                ],
                'field' => 'unit_fk',
                'alias' => 'unit_id',
                'filter' => 'where'
            ],
            'github' => [
                'type' => 'in',
                'field' => 'github',
                'alias' => 'student_github',
                'filter' => 'none',
            ],
            'linkedin' => [
                'type' => 'in',
                'field' => 'linkedin',
                'alias' => 'student_linkedin',
                'filter' => 'none',
            ],
            'cv' => [
                'type' => 'in',
                'field' => 'cv',
                'alias' => 'student_cv',
                'filter' => 'none',
            ],
            'plesk' => [
                'type' => 'in',
                'field' => 'plesk',
                'alias' => 'student_plesk',
                'filter' => 'none',
            ],
            'personal_website' => [
                'type' => 'in',
                'field' => 'personal_website',
                'alias' => 'student_personal_website',
                'filter' => 'none',
            ],
            'calendar_id' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['applicant', 'promotion_fk'], 'right' => ['promotion', 'id']],
                    ['left' => ['promotion', 'id'], 'right' => ['calendar', 'promotion_fk']]
                ],
                'field' => 'id',
                'alias' => 'student_calendar_id',
                'filter' => 'where'
            ],
            'promotion_start_date' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['student', 'calendar_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'start_date',
                'alias' => 'promotion_start_date',
                'filter' => 'where'
            ],
            'promotion_end_date' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['student', 'calendar_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'end_date',
                'alias' => 'promotion_end_date',
                'filter' => 'where'
            ],
            'promotion_duration' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']],
                    ['left' => ['student', 'calendar_fk'], 'right' => ['promotion', 'id']]
                ],
                'field' => 'duration',
                'alias' => 'promotion_duration',
                'filter' => 'where'
            ],
            'birthdate' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' => 'birthdate',
                'alias' => 'birthdate',
                'filter' => 'where'
            ],
            'birthplace' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' => 'birthplace',
                'alias' => 'birthplace',
                'filter' => 'where'
            ],
            'badge' => [
                'type' => 'in',
                'field' => 'badge',
                'alias' => 'student_badge',
                'filter' => 'like',
            ],
            'personal_email' => [
                'type' => 'out',
                'link' => [
                    ['left' => ['student', 'applicant_fk'], 'right' => ['applicant', 'id']]
                ],
                'field' => 'personal_email',
                'alias' => 'personal_email',
                'filter' => 'where'
            ],
        ]);
    }
}
