<?php
class Student_Access
{
    private $controller;

    public function __construct(&$controller)
    {
        $this->controller = &$controller;
    }

    public function access($call, $payload, &$params)
    {

        $granted = [
            'getClass',
            'getPromotion',
        ];

        $limited = [
            'getAbsence',
            'postAbsence',

            'getActivityAttendance',

            'getAlert',

            'getApplicant',

            'getStudentCalendar',

            'getCalendarDay',

            'getCalendarHistory',

            'getGroup',
            'postGroup',

            'getGroupReview',
            'getGroupAvailable',
            'getWaitingList',
            'postWaitingList',
            'putWaitingList',
            'deleteWaitingList',
            'putGroupClick',

            'getJob',
            'getJobAwait',
            'getJobDone',
            'getJobProgress',
            'getJobReady',
            'getJobSkill',

            'getLogtime',

            'getLogtime_Event',

            'getPromotion',

            'getPromotionHistory',

            'getRegistration',

            'getStudent',
            'putStudent',

            'getStudentSkill',
            'getStudentSkillTotal',
            'getStudentClassTotal',
            'getStudentPromotionClassTotal',
            'getStudentJobAvailable',

            'getStudentUnit',
            'getUnitJob',
            'getUnitGoal',
            'getPromotionUnit',
        ];

        if (in_array($call, $granted)) {
            return (true);
        }

        if (in_array($call, $limited)) {
            return ($this->$call($payload, $params));
        }

        return (false);
    }

    // GET

    private function getActivityAttendance($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getCalendarHistory($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getStudent($payload, &$params)
    {

        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getStudentCalendar($payload, &$params)
    {
        $this->controller->load->model('Student_Model');
        $data = [
            'student_id' => $payload['scope'][0],
            'calendar_id' => '',
        ];
        $calendar_id = $this->controller->Student_Model->getStudent($data);
        if (count($calendar_id) == 1) {
            return $calendar_id[0]['student_calendar_id'];
        } else {
            return false;
        }
    }

    private function getCalendarDay($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];
        $params['calendar_id'] = $this->getStudentCalendar($payload, $params);
        if ($params['calendar_id'] === false) {
            return false;
        }
        return (true);
    }

    private function getPromotionHistory($payload, &$params)
    {

        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getPromotionUnit($payload, &$params)
    {

        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getApplicant($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getAbsence($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getPromotion($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getRegistration($payload, &$params)
    {

        if (isset($params['registration_id']) && $params['registration_id'] !== '') {
            $this->controller->load->model('Registration_Model');

            $regparams = [
                'registration_id' => $params['registration_id'],
                'group_is_valid' => ''
            ];

            $registration = $this->controller->Registration_Model->getRegistration($regparams);
            if ($registration[0]['group_is_valid'] !== "1") unset($params['link_subject']);
        } else {
            unset($params['link_subject']);
        }

        $params['member_id'] = $payload['scope'][0];

        return (true);
    }

    private function getJob($payload, &$params)
    {
        // mettre comme getjob skill et filtrer le sujet et le guide pour pas le send chaque fois ...
        return (false);
    }

    private function getJobSkill($payload, &$params)
    {
        if (
            !array_key_exists('job_id', $params)
            || gettype($params['job_id']) != "string"
            || strlen($params['job_id']) <= 0
        ) {
            return (true);
        }

        $jobsavailable = [
            'student_id' => $payload['scope'][0],
            'job_id' => $params['job_id']
        ];
        $this->controller->load->model('Job_Model');
        $jobs = $this->controller->Job_Model->getStudentJobAvailable($jobsavailable);
        if (count($jobs) != 1 || array_key_exists('Error', $jobs)) {
            $regparams = [
                'member_id' => $payload['scope'][0],
                'job_id' => $params['job_id']
            ];
            $this->controller->load->model('Registration_Model');
            $reg = $this->controller->Registration_Model->getRegistration($regparams);
            if (count($reg) > 0 && !array_key_exists('Error', $reg)) {
                return (true);
            }

            return (false);
        }

        return (true);
    }

    private function getStudentUnit($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getUnitJob($payload, &$params)
    {
        if (
            !array_key_exists('unit_id', $params)
            || gettype($params['unit_id']) != "string"
            || strlen($params['unit_id']) <= 0
        ) {
            return (true);
        }

        $viewer = [
            'student_id' => $payload['scope'][0],
            'unit_id' => $params['unit_id']
        ];
        $this->controller->load->model('Unit_Viewer_Model');
        $unit = $this->controller->Unit_Viewer_Model->getStudentUnit($viewer);
        if (count($unit) == 1 && !array_key_exists('Error', $unit)) {
            return (true);
        }

        return (false);
    }

    private function getUnitGoal($payload, &$params)
    {
        if (
            !array_key_exists('unit_id', $params)
            || gettype($params['unit_id']) != "string"
            || strlen($params['unit_id']) <= 0
        ) {
            return (true);
        }

        $viewer = [
            'student_id' => $payload['scope'][0],
            'unit_id' => $params['unit_id']
        ];
        $this->controller->load->model('Unit_Viewer_Model');
        $unit = $this->controller->Unit_Viewer_Model->getStudentUnit($viewer);
        if (count($unit) == 1 && !array_key_exists('Error', $unit)) {
            return (true);
        }

        return (false);
    }

    private function getGroup($payload, &$params)
    {
        if (
            !array_key_exists('group_id', $params)
            || gettype($params['group_id']) != "string"
            || strlen($params['group_id']) <= 0
        ) {
            return (true);
        }

        $regparams = [
            'group_id' => $params['group_id'],
            'member_id' => $payload['scope'][0]
        ];
        $this->controller->load->model('Registration_Model');
        $reg = $this->controller->Registration_Model->getRegistration($regparams);
        if (count($reg) == 1 && !array_key_exists('Error', $reg)) {
            return (true);
        }

        return (false);
    }

    private function getJobAwait($payload, &$params)
    {
        $params['member_id'] = $payload['scope'][0];
        unset($params['link_subject']);

        return (true);
    }

    private function getJobProgress($payload, &$params)
    {
        $params['member_id'] = $payload['scope'][0];

        return (true);
    }

    private function getJobReady($payload, &$params)
    {
        $params['member_id'] = $payload['scope'][0];

        return (true);
    }

    private function getJobDone($payload, &$params)
    {
        $params['member_id'] = $payload['scope'][0];

        return (true);
    }

    private function getStudentSkill($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getGroupReview($payload, &$params)
    {
        return ($this->getGroup($payload, $params));
    }

    private function getStudentSkillTotal($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getStudentClassTotal($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getStudentPromotionClassTotal($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getStudentJobAvailable($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getGroupAvailable($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getWaitingList($payload, &$params)
    {
        if (
            array_key_exists('group_id', $params)
            && gettype($params['group_id']) == "string"
            && strlen($params['group_id']) > 0
        ) {
            $regparams = [
                'group_id' => $params['group_id'],
                'lead_id' => $payload['scope'][0]
            ];
            $this->controller->load->model('Registration_Model');
            $reg = $this->controller->Registration_Model->getRegistration($regparams);
            if (count($reg) >= 1 && $this->controller->Registration_Model->Status()->IsValid()) {
                return (true);
            }
            return (false);
        }

        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getLogtime($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getLogtime_Event($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    private function getAlert($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    // POST
    private function postGroup($payload, &$params)
    {
        if (
            !array_key_exists('job_id', $params)
            || !array_key_exists('group_name', $params)
            || strlen($params['job_id']) <= 0
            || strlen($params['group_name']) <= 0
            || gettype($params['job_id']) != "string"
            || gettype($params['group_name']) != "string"
        ) {
            return (true);
        }

        $params['student_id'] = $payload['scope'][0];

        $this->controller->load->model('Job_Model');
        $jobs = $this->controller->Job_Model->getStudentJobAvailable($params);
        if (count($jobs) == 1 && !array_key_exists('Error', $jobs)) {
            if ($jobs[0]['prerequisites'] == '1') {
                return (true);
            }
            return (false);
        }

        return (false);
    }

    private function postAbsence($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];
        $params['email'] = $payload['user_email'];

        return (true);
    }

    private function postWaitingList($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];

        return (true);
    }

    // PUT
    private function putGroupClick($payload, &$params)
    {
        if (
            !array_key_exists('group_id', $params)
            || gettype($params['group_id']) != "string"
            || strlen($params['group_id']) <= 0
        ) {
            return (true);
        }

        $regparams = [
            'group_id' => $params['group_id'],
            'lead_id' => $payload['scope'][0],
            'limit' => '1'
        ];
        $this->controller->load->model('Registration_Model');
        $reg = $this->controller->Registration_Model->getRegistration($regparams);
        if (count($reg) == 1 && $this->controller->Status()->IsValid()) {
            $params['click'] = '1';

            return (true);
        }

        return (false);
    }

    private function putWaitingList($payload, &$params)
    {
        if (
            !array_key_exists('group_id', $params)
            || gettype($params['group_id']) != "string"
            || strlen($params['group_id']) <= 0
        ) {
            return (true);
        }

        $regparams = [
            'group_id' => $params['group_id'],
            'lead_id' => $payload['scope'][0],
            'member_is_lead' => '1'
        ];
        $this->controller->load->model('Registration_Model');
        $reg = $this->controller->Registration_Model->getRegistration($regparams);
        if (count($reg) == 1 && $this->controller->Status()->IsValid()) {
            return (true);
        }

        return (false);
    }

    private function putStudent($payload, &$params)
    {
        $params['student_id'] = $payload['scope'][0];
        unset($params['firstname']);
        unset($params['lastname']);
        unset($params['student_email']);
        unset($params['current_unit_id']);
        unset($params['promotion_id']);

        return (true);
    }

    private function deleteWaitingList($payload, &$params)
    {
        if (
            !array_key_exists('group_id', $params)
            || gettype($params['group_id']) != "string"
            || strlen($params['group_id']) <= 0
        ) {
            return (false);
        }

        $params['student_id'] = $payload['scope'][0];

        return (true);
    }
}
