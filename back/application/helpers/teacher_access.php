<?php

class Teacher_Access
{
    private $controller;

    public function __construct(&$controller)
    {
        $this->controller = &$controller;
    }

    public function Access($call, $payload, &$params)
    {
        $granted = [
            'getClass',
            'getSection',
            'getSkill',
        ];

        $limited = [
            'getAbsence',
            'getActivity',
            'postActivity',
            'putActivity',
            'deleteActivity',

            'getActivityAttendance',
            'postActivityAttendance',
            'putActivityAttendance',

            'getAlternance',
            'putAlternance',
            'postAlternance',

            'getApplicant',

            'getAlert',
            'postAlert',
            'putAlert',

            'getCalendar',

            'getCalendarDay',
            'postCalendarDay',
            'putCalendarDay',
            'deleteCalendarDay',

            'getCalendarHistory',

            'getFollowup',
            'postFollowup',
            'getStudentFollowup',
            'postStudentFollowup',

            'getGroup',
            'postGroup',
            'putGroup',
            'deleteGroup',
            'putGroupValidity',
            'getGroupReview',
            'putGroupReview',
            'postMember',
            'deleteMember',

            'getJob',
            'postJob',
            'putJob',
            'deleteJob',

            'getJobStudentAvailable',

            'getJobSkill',
            'postJobSkill',
            'putJobSkill',
            'deleteJobSkill',

            'getJobStudent',
            'deleteJobStudent',

            'getJobProgress',
            'getJobAwait',
            'getJobReady',
            'getJobChecked',
            'getJobDone',

            'getLogtime',
            'getLastLog',
            'getLogtime_Event',
            'postLogtime_Event',

            'getStudentLastLogtime',

            'getPromotion',

            'getPromotionHistory',

            'getPromotionLastLogtime',
            'getPromotionLogtimeAverage',
            'getPromotionLogtime',

            'getRegistration',

            'getStudent',

            'getStudentSkill',
            'putStudentSkill',
            'getStudentUnit',
            'getStudentJobAvailable',
            'getStudentPromotionClassTotal',
            'getStudentClassTotal',

            'getUnit',
            'putUnit',
            'getUnitStudent',
            'postUnitStudent',
            'deleteUnitStudent',
            'postUnitStudents',
            'deleteUnitStudents',
            'getStudentSkillTotal',
            'getUnitJob',
            'postUnitJob',
            'postUnitStudentsCurrent',
            'getUnitGoal',
            'postUnitGoal',
            'putUnitGoal',
            'putUnitToEnd',
            'deleteUnitGoal',

            'getUnitHistory',

            'getPromotionUnit',
            'getUnitCompleted',
            'putJobReview',
            'putUnitReview',
            'postUnitRevalidate',
            'getPromotionClassTotal',
            'getStudentInactive',

        ];

        if (in_array($call, $granted)) {
            return (true);
        }

        if (in_array($call, $limited)) {
            return ($this->$call($payload, $params));
        }

        return (false);
    }

    private function isUnitInScope($payload, &$params)
    {
        if (
            !array_key_exists('unit_id', $params)
            || gettype($params['unit_id']) != "string"
            || strlen($params['unit_id']) <= 0
        ) {
            return (false);
        }

        foreach ($payload['scope'] as $scope) {
            if ($params['unit_id'] == $scope) {
                return (true);
            }
        }

        return (false);
    }

    private function isUnitGoalInScope($payload, &$params)
    {

        if (
            !array_key_exists('unit_goal_id', $params)
            || gettype($params['unit_goal_id']) != "string"
            || strlen($params['unit_goal_id']) <= 0
        ) {
            return (false);
        }

        $this->controller->load->model('Unit_Goal_Model');
        $data = [
            'unit_goal_id' => $params['unit_goal_id'],
            'unit_id' => ''
        ];
        $res = $this->controller->Unit_Goal_Model->getUnitGoal($data);

        if (count($res) <= 0 || !$this->controller->Unit_Goal_Model->Status()->IsValid()) {
            return (false);
        }


        foreach ($payload['scope'] as $scope) {
            if ($res[0]['unit_id'] == $scope) {
                return (true);
            }
        }

        return (false);
    }

    private function areUnitsInScope($payload, &$params, $field = "unit_id")
    {
        $asked_units = [];
        if (array_key_exists($field, $params)) {
            if (gettype($params[$field]) == 'string' && strlen($params[$field]) > 0)
                $params[$field] = array($params[$field]);
            if (gettype($params[$field]) == 'array')
                $asked_units = array_merge($asked_units, $params[$field]);
        }

        if (count($asked_units) == 0) {
            $asked_units = $payload['scope'];
        }

        $units = [];
        foreach ($asked_units as $punit) {
            foreach ($payload['scope'] as $scope) {
                if ($scope == $punit) {
                    array_push($units, $scope);
                }
            }
        }
        unset($params[$field]);
        if (count($units) > 0)
            $params[$field] = $units;
        else
            return (false);

        return (true);
    }

    private function isJobInScope($payload, &$params)
    {
        if (
            !array_key_exists('job_id', $params)
            || gettype($params['job_id']) != "string"
            || strlen($params['job_id']) <= 0
        ) {
            return (false);
        }

        $data = [
            'job_id' => $params['job_id'],
            'unit_id' => $payload['scope']
        ];
        $this->controller->load->model('Job_Model');
        $res = $this->controller->Job_Model->getJob($data);
        if (count($res) <= 0 || !$this->controller->Job_Model->Status()->IsValid()) {
            return (false);
        }

        return (true);
    }

    private function isGroupInScope($payload, &$params)
    {
        if (
            !array_key_exists('group_id', $params)
            || gettype($params['group_id']) != "string"
            || strlen($params['group_id']) <= 0
        ) {
            return (false);
        }

        $data = [
            'group_id' => $params['group_id'],
            'job_unit_id' => $payload['scope']
        ];
        $this->controller->load->model('Registration_Model');
        $res = $this->controller->Registration_Model->getRegistration($data);
        if (count($res) <= 0 || !$this->controller->Registration_Model->Status()->IsValid()) {
            return (false);
        }

        foreach ($payload['scope'] as $scope) {
            if ($res[0]['job_unit_id'] == $scope) {
                return (true);
            }
        }

        return (false);
    }

    private function isStudentInScope($payload, &$params)
    {
        if (isset($params['applicant_id']) && !isset($params['student_id'])) {
            $this->controller->load->model('Student_Model');
            $data = [
                'student_id' => '',
                'applicant_id' => $params['applicant_id']
            ];
            $res = $this->controller->Student_Model->getStudent($data);
            if (count($res) <= 0 || !$this->controller->Student_Model->Status()->IsValid()) {
                return (false);
            }
            $params['student_id'] = $res[0]['student_id'];
        }

        if (
            !array_key_exists('student_id', $params)
            || gettype($params['student_id']) != "string"
            || strlen($params['student_id']) <= 0
        ) {
            return (false);
        }

        $data = [
            'student_id' => $params['student_id'],
            'unit_id' => '',
        ];
        $this->controller->load->model('Unit_Viewer_Model');
        $res = $this->controller->Unit_Viewer_Model->getStudentUnit($data);
        if (count($res) <= 0 || !$this->controller->Unit_Viewer_Model->Status()->IsValid()) {
            return (false);
        }

        foreach ($res as $unit) {
            foreach ($payload['scope'] as $scope) {
                if ($unit['unit_id'] == $scope) {
                    return (true);
                }
            }
        }

        return (false);
    }

    private function areStudentsInScope($payload, &$params)
    {
        $asked_students = [];
        if (array_key_exists('student_id', $params)) {
            if (gettype($params['student_id']) == 'string' && strlen($params['student_id']) > 0)
                $params['student_id'] = array($params['student_id']);
            if (gettype($params['student_id']) == 'array')
                $asked_students = array_merge($asked_students, $params['student_id']);
        }

        foreach ($asked_students as $student_id) {
            $p = ['student_id' => $student_id];
            if ($this->isStudentInScope($payload, $p) == false)
                return (false);
        }

        return (true);
    }

    private function isPromotionInScope($payload, &$params)
    {
        if (
            !array_key_exists('promotion_id', $params)
            || gettype($params['promotion_id']) != "string"
            || strlen($params['promotion_id']) <= 0
        ) {
            return (false);
        }

        $data_promotion_unit = [
            'promotion_id' => $params['promotion_id'],
            'unit_id' => $payload['scope']
        ];

        $this->controller->load->model('Promotion_Unit_Model');
        $res = $this->controller->Promotion_Unit_Model->getPromotionUnit($data_promotion_unit);

        if (count($res) <= 0 || !$this->controller->Promotion_Unit_Model->Status()->IsValid()) {
            return (false);
        }

        return true;
    }

    // GET

    private function getAbsence($payload, &$params)
    {
        $params['current_unit_id'] = $payload['scope'];
        return true;
    }

    private function getActivity($payload, &$params)
    {
        if (isset($params['unit_id'])) {
            return ($this->isUnitInScope($payload, $params));
        } else {
            $params['unit_id'] = $payload['scope'];
            return true;
        }
    }

    private function postActivity($payload, &$params)
    {
        $params['author'] = $payload['user_email'];
        return ($this->isUnitInScope($payload, $params));
    }

    private function putActivity($payload, &$params)
    {
        if (!isset($params['unit_id']) && isset($params['activity_id'])) {
            $this->controller->load->model('Activity_Model');
            $data = [
                'activity_id' => $params['activity_id'],
                'unit_id' => ''
            ];
            $res = $this->controller->Activity_Model->getActivity($data);
            if (count($res) <= 0 || !$this->controller->Activity_Model->Status()->IsValid()) {
                return (false);
            }
            $params['unit_id'] = $res[0]['unit_id'];
        }
        return ($this->isUnitInScope($payload, $params));
    }

    private function deleteActivity($payload, &$params) {
        if (!isset($params['unit_id']) && isset($params['activity_id'])) {
            $this->controller->load->model('Activity_Model');
            $data = [
                'activity_id' => $params['activity_id'],
                'unit_id' => ''
            ];
            $res = $this->controller->Activity_Model->getActivity($data);
            if (count($res) <= 0 || !$this->controller->Activity_Model->Status()->IsValid()) {
                return (false);
            }
            $params['unit_id'] = $res[0]['unit_id'];
        }
        return ($this->isUnitInScope($payload, $params));
    }

    private function getActivityAttendance($payload, &$params)
    {
        if (isset($params['promotion_id']) && $params['promotion_id'] !== '') {
            return ($this->isPromotionInScope($payload, $params));
        } else if (isset($params['student_id']) && $params['student_id'] !== '') {
            return ($this->isStudentInScope($payload, $params));
        } else {
            $params['unit_id'] = $payload['scope'];
            return true;
        }
    }

    private function postActivityAttendance($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function putActivityAttendance($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getAlert($payload, &$params)
    {
        if (isset($params['student_id']) && $params['student_id'] != '' || isset($params['applicant_id']) && $params['applicant_id'] != '') {
            return ($this->isStudentInScope($payload, $params));
        } elseif (isset($params['promotion_id']) && $params['promotion_id'] != '') {
            return ($this->isPromotionInScope($payload, $params));
        } else {
            $params['current_unit_id'] = $payload['scope'];
            return true;
        }
    }

    private function getCalendar($payload, &$params)
    {
        if (!isset($params['promotion_id']) && isset($params['id']) && $params['id'] != '') {
            $this->controller->load->model('Calendar_Model');
            $data = [
                'id' => $params['id'],
                'promotion_id' => ''
            ];
            $res = $this->controller->Calendar_Model->getCalendar($data);
            if (count($res) <= 0 || !$this->controller->Calendar_Model->Status()->IsValid()) {
                return (false);
            }
            $params['promotion_id'] = $res[0]['promotion_id'];
            return ($this->isPromotionInScope($payload, $params));
        } else {
            $this->controller->load->model('Promotion_Unit_Model');
            $data = [
                'unit_id' => $payload['scope'],
                'promotion_id' => ''
            ];
            $res = $this->controller->Promotion_Unit_Model->getPromotionUnit($data);
            if (count($res) <= 0 || !$this->controller->Promotion_Unit_Model->Status()->IsValid()) {
                return (false);
            }
            $params['promotion_id'] = [];
            foreach ($res as $promotion) {
                array_push($params['promotion_id'], $promotion['promotion_id']);
            }
            return true;
        }
    }

    private function getCalendarDay($payload, &$params)
    {
        if (!isset($params['promotion_id']) && isset($params['calendar_id'])) {
            $this->controller->load->model('Calendar_Model');
            $data = [
                'id' => $params['calendar_id'],
                'promotion_id' => ''
            ];
            $res = $this->controller->Calendar_Model->getCalendar($data);
            if (count($res) <= 0 || !$this->controller->Calendar_Day_Model->Status()->IsValid()) {
                return (false);
            }
            $params['promotion_id'] = $res[0]['promotion_id'];
        }
        return ($this->isPromotionInScope($payload, $params));
    }

    private function getCalendarHistory($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getFollowup($payload, &$params)
    {
        $params['current_unit_id'] = $payload['scope'];
        return true;
    }

    private function getGroup($payload, &$params)
    {
        return ($this->isGroupInScope($payload, $params));
    }

    private function getJob($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function getStudentSkill($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getJobStudentAvailable($payload, &$params)
    {
        return ($this->isJobInScope($payload, $params));
    }

    private function getJobSkill($payload, &$params)
    {
        return ($this->isJobInScope($payload, $params));
    }

    private function putStudentSkill($payload, &$params)
    {
        if ($this->isStudentInScope($payload, $params) == false) {
            return (false);
        }

        return ($this->isJobInScope($payload, $params));
    }

    private function getStudentUnit($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getStudentJobAvailable($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getStudentPromotionClassTotal($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getStudentClassTotal($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function postFollowup($payload, &$params)
    {
        $params['type'] = "PEDA";
        return ($this->isStudentInScope($payload, $params));
    }

    private function postJob($payload, &$params)
    {
        return ($this->isUnitInScope($payload, $params));
    }

    private function putJob($payload, &$params)
    {
        return ($this->isJobInScope($payload, $params));
    }

    private function deleteJob($payload, &$params)
    {
        return ($this->isJobInScope($payload, $params));
    }

    private function postJobSkill($payload, &$params)
    {
        return ($this->isJobInScope($payload, $params));
    }

    private function putJobSkill($payload, &$params)
    {
        return ($this->isJobInScope($payload, $params));
    }

    private function deleteJobSkill($payload, &$params)
    {
        return ($this->isJobInScope($payload, $params));
    }

    private function getRegistration($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params, 'job_unit_id'));
    }

    private function getJobStudent($payload, &$params)
    {
        return ($this->isJobInScope($payload, $params));
    }

    private function deleteJobStudent($payload, &$params)
    {
        return ($this->isJobInScope($payload, $params));
    }

    private function getJobAwait($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params, 'job_unit_id'));
    }

    private function getJobProgress($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params, 'job_unit_id'));
    }

    private function getJobReady($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params, 'job_unit_id'));
    }

    private function getJobChecked($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params, 'job_unit_id'));
    }

    private function getJobDone($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params, 'job_unit_id'));
    }

    private function getUnitCompleted($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function getUnitHistory($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function postGroup($payload, &$params)
    {
        if ($this->isStudentInScope($payload, $params) == false)
            return (false);

        return ($this->isJobInScope($payload, $params));
    }

    private function putGroup($payload, &$params)
    {
        return ($this->isGroupInScope($payload, $params));
    }

    private function deleteGroup($payload, &$params)
    {
        return ($this->isGroupInScope($payload, $params));
    }

    private function putGroupValidity($payload, &$params)
    {
        return ($this->isGroupInScope($payload, $params));
    }

    private function getGroupReview($payload, &$params)
    {
        return ($this->isGroupInScope($payload, $params));
    }

    private function putGroupReview($payload, &$params)
    {
        return ($this->isGroupInScope($payload, $params));
    }

    private function postMember($payload, &$params)
    {
        if ($this->isStudentInScope($payload, $params) == false)
            return (false);

        return ($this->isGroupInScope($payload, $params));
    }

    private function deleteMember($payload, &$params)
    {
        if ($this->isStudentInScope($payload, $params) == false)
            return (false);

        return ($this->isGroupInScope($payload, $params));
    }

    private function getApplicant($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getStudent($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params, "student_unit"));
    }

    private function getUnit($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function putUnit($payload, &$params)
    {
        return ($this->isUnitInScope($payload, $params));
    }

    private function getUnitStudent($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function postUnitStudent($payload, &$params)
    {
        if ($this->isUnitInScope($payload, $params) == false)
            return (false);

        return ($this->isStudentInScope($payload, $params));
    }

    private function deleteUnitStudent($payload, &$params)
    {
        if ($this->isUnitInScope($payload, $params) == false)
            return (false);

        return ($this->isStudentInScope($payload, $params));
    }

    private function postUnitStudents($payload, &$params)
    {
        if ($this->isUnitInScope($payload, $params) == false)
            return (false);

        return ($this->areStudentsInScope($payload, $params));
    }

    private function deleteUnitStudents($payload, &$params)
    {
        if ($this->isUnitInScope($payload, $params) == false)
            return (false);

        return ($this->areStudentsInScope($payload, $params));
    }

    private function getStudentSkillTotal($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getUnitJob($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function postUnitJob($payload, &$params) //
    {
        return ($this->isUnitInScope($payload, $params));
    }

    private function postUnitStudentsCurrent($payload, &$params)
    {
        if ($this->isUnitInScope($payload, $params) == false)
            return (false);

        return ($this->areStudentsInScope($payload, $params));
    }

    private function getUnitGoal($payload, &$params)
    {
        return ($this->isUnitInScope($payload, $params));
    }

    private function postUnitGoal($payload, &$params)
    {
        return ($this->isUnitInScope($payload, $params));
    }

    private function putUnitGoal($payload, &$params)
    {
        return ($this->isUnitInScope($payload, $params));
    }

    private function putUnitToEnd($payload, &$params)
    {
        return ($this->isUnitInScope($payload, $params));
    }

    private function deleteUnitGoal($payload, &$params)
    {
        return ($this->isUnitGoalInScope($payload, $params));
    }

    private function getPromotion($payload, &$params)
    {
        if (!isset($params['promotion_id']) || $params['promotion_id'] == "") {
            $params['promotion_id'] = $this->getPromotionInScope($payload);
            return true;
        } else {
            return ($this->isPromotionInScope($payload, $params));
        }
    }

    private function getPromotionInScope($payload)
    {
        $this->controller->load->model('Promotion_Unit_Model');
        $units = [];
        foreach ($payload['scope'] as $scope) {
            array_push($units, $scope);
        }
        $data = [
            'unit_id' => $units,
            'promotion_id' => ''
        ];

        $promotions_in_scope = $this->controller->Promotion_Unit_Model->getPromotionUnit($data);
        $promotions = [];

        foreach ($promotions_in_scope as $promotion) {
            array_push($promotions, $promotion['promotion_id']);
        }

        return $promotions;
    }

    private function getPromotionHistory($payload, &$params)
    {
        if (isset($params['promotion_id']) && !isset($params['student_id'])) {
            return ($this->isPromotionInScope($payload, $params));
        } else if (!isset($params['promotion_id']) && isset($params['student_id'])) {
            return ($this->isStudentInScope($payload, $params));
        } else {
            return ($this->isStudentInScope($payload, $params));
        }
    }

    private function getPromotionUnit($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function putJobReview($payload, &$params)
    {
        return ($this->isJobInScope($payload, $params));
    }

    private function putUnitReview($payload, &$params)
    {
        return ($this->isUnitInScope($payload, $params));
    }

    private function postUnitRevalidate($payload, &$params)
    {
        return ($this->isUnitInScope($payload, $params));
    }

    private function getPromotionClassTotal($payload, &$params)
    {
        return ($this->isPromotionInScope($payload, $params));
    }

    private function getStudentInactive($payload, &$params)
    {
        return ($this->isPromotionInScope($payload, $params));
    }

    private function getPromotionLastLogtime($payload, &$params)
    {
        return ($this->isPromotionInScope($payload, $params));
    }

    private function getPromotionLogtimeAverage($payload, &$params)
    {
        return ($this->isPromotionInScope($payload, $params));
    }

    private function getLogtime($payload, &$params)
    {
        if (isset($params['promotion_id'])) {
            return ($this->isPromotionInScope($payload, $params));
        }
        return ($this->isStudentInScope($payload, $params));
    }

    private function getLastLog($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getStudentLastLogtime($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getLogtime_Event($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getPromotionLogtime($payload, &$params)
    {
        return ($this->isPromotionInScope($payload, $params));
    }

    private function postAlert($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function postLogtime_Event($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function putAlert($payload, &$params)
    {
        if (isset($params['student_id'])) unset($params['student_id']);
        $this->controller->load->model('Alert_Model');
        $data = [
            'alert_id' => $params['alert_id'],
            'student_id' => ''
        ];
        $res = $this->controller->Alert_Model->getAlert($data);
        if (count($res) <= 0 || !$this->controller->Alert_Model->Status()->IsValid()) {
            return (false);
        }
        $params['student_id'] = $res[0]['student_id'];
        return ($this->isStudentInScope($payload, $params));
    }

    private function putCalendarDay($payload, &$params)
    {
        if (!isset($params['promotion_id']) && !isset($params['calendar_id'])) {
            $this->controller->load->model('Calendar_Day_Model');
            $data = [
                'id' => $params['id'],
                'calendar_id' => ''
            ];
            $res = $this->controller->Calendar_Day_Model->getCalendarDay($data);
            if (count($res) <= 0 || !$this->controller->Calendar_Day_Model->Status()->IsValid()) {
                return (false);
            }
            $params['calendar_id'] = $res[0]['calendar_id'];
        }
        if (!isset($params['promotion_id']) && isset($params['calendar_id'])) {
            $this->controller->load->model('Calendar_Model');
            $data = [
                'id' => $params['calendar_id'],
                'promotion_id' => ''
            ];
            $res = $this->controller->Calendar_Model->getCalendar($data);
            if (count($res) <= 0 || !$this->controller->Calendar_Model->Status()->IsValid()) {
                return (false);
            }
            $params['promotion_id'] = $res[0]['promotion_id'];
        }
        return ($this->isPromotionInScope($payload, $params));
    }

    private function deleteCalendarDay($payload, &$params)
    {
        if (!isset($params['promotion_id']) && !isset($params['calendar_id'])) {
            $this->controller->load->model('Calendar_Day_Model');
            $data = [
                'id' => $params['id'],
                'calendar_id' => ''
            ];
            $res = $this->controller->Calendar_Day_Model->getCalendarDay($data);
            if (count($res) <= 0 || !$this->controller->Calendar_Day_Model->Status()->IsValid()) {
                return (false);
            }
            $params['calendar_id'] = $res[0]['calendar_id'];
        }
        if (!isset($params['promotion_id']) && isset($params['calendar_id'])) {
            $this->controller->load->model('Calendar_Model');
            $data = [
                'id' => $params['calendar_id'],
                'promotion_id' => ''
            ];
            $res = $this->controller->Calendar_Model->getCalendar($data);
            if (count($res) <= 0 || !$this->controller->Calendar_Model->Status()->IsValid()) {
                return (false);
            }
            $params['promotion_id'] = $res[0]['promotion_id'];
        }
        return ($this->isPromotionInScope($payload, $params));
    }

    private function postCalendarDay($payload, &$params)
    {
        if (!isset($params['promotion_id']) && isset($params['calendar_id'])) {
            $this->controller->load->model('Calendar_Model');
            $data = [
                'id' => $params['calendar_id'],
                'promotion_id' => ''
            ];
            $res = $this->controller->Calendar_Model->getCalendar($data);
            if (count($res) <= 0 || !$this->controller->Calendar_Model->Status()->IsValid()) {
                return (false);
            }
            $params['promotion_id'] = $res[0]['promotion_id'];
        }
        return ($this->isPromotionInScope($payload, $params));
    }

    private function getStudentFollowup($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function postStudentFollowup($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getAlternance($payload, &$params)
    {
        if (isset($params['id']) && $params['id'] !== '') {
            $this->controller->load->model('Alternance_Model');
            $data = [
                'id' => $params['id'],
                'student_id' => ''
            ];
            $res = $this->controller->Alternance_Model->getAlternance($data);

            if (count($res) <= 0 || !$this->controller->Alternance_Model->Status()->IsValid()) {
                return (false);
            }
            $params['student_id'] = $res[0]['student_id'];
            return ($this->isStudentInScope($payload, $params));
        } else if (isset($params['promotion_id']) && !isset($params['student_id'])) {
            return ($this->isPromotionInScope($payload, $params));
        } else if (!isset($params['promotion_id']) && isset($params['student_id'])) {
            return ($this->isStudentInScope($payload, $params));
        } else {
            $params['current_unit_id'] = $payload['scope'];
            return true;
        }
    }

    private function putAlternance($payload, &$params)
    {
        if (isset($params['id']) && $params['id'] !== '') {
            $this->controller->load->model('Alternance_Model');
            $data = [
                'id' => $params['id'],
                'student_id' => ''
            ];
            $res = $this->controller->Alternance_Model->getAlternance($data);

            if (count($res) <= 0 || !$this->controller->Alternance_Model->Status()->IsValid()) {
                return (false);
            }
            $params['student_id'] = $res[0]['student_id'];
            if ($this->isStudentInScope($payload, $params) == true) {
                unset($params['student_id']);
                return true;
            }
        }
        return false;
    }

    // private function postAlternance($payload, &$params)
    // {
    //     return ($this->isStudentInScope($payload, $params));
    // }
}
