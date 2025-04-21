<?php

class Peda_Access
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
            'getPromotion',
            'getSection',
            'getSkill',
        ];

        $limited = [
            'getAbsence',

            'getActivity',
            'postActivity',
            'putActivity',

            'getActivityAttendance',
            'postActivityAttendance',
            'putActivityAttendance',

            'getAlert',

            'getCalendar',

            'getCalendarDay',

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
            'getJobStudentAvailable',
            'getJobSkill',
            'getJobStudent',
            'getJobAwait',
            'getJobProgress',
            'getJobReady',
            'getJobChecked',
            'getJobDone',
            'deleteJobStudent',

            'getLogtime',
            'getLastLog',

            'getStudentLastLogtime',

            'getLogtime_Event',

            'getPromotionHistory',

            'getRegistration',

            'getStudent',

            'getStudentSkill',
            'getStudentUnit',
            'getStudentJobAvailable',
            'getStudentPromotionClassTotal',
            'getStudentClassTotal',

            'getUnit',
            'getUnitJob',
            'getUnitGoal',
            'getUnitCompleted',
            'getUnitStudent',
            'getPromotionUnit',

            'getUnitHistory',

            'getStudentSkillTotal',
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
        }
        else {
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
        if(!isset($params['unit_id']) && isset($params['activity_id']))
        {
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
        return ($this->isStudentInScope($payload, $params));
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

    private function getLogtime($payload, &$params)
    {
        if (isset($params['promotion_id'])) {
            return ($this->isPromotionInScope($payload, $params));
        }
        return ($this->isStudentInScope($payload, $params));
    }

    private function getLogtime_Event($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getCalendarHistory($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getStudentSkill($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
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

    private function getStudentLastLogtime($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getLastLog($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getFollowup($payload, &$params)
    {
        $params['type'] = "PEDA";
        return ($this->isStudentInScope($payload, $params));
    }

    private function getStudentFollowup($payload, &$params)
    {
        $params['type'] = "PEDA";
        return ($this->isStudentInScope($payload, $params));
    }

    private function postStudentFollowup($payload, &$params)
    {
        $params['type'] = "PEDA";
        return ($this->isStudentInScope($payload, $params));
    }

    private function getJob($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function getJobStudentAvailable($payload, &$params)
    {
        return ($this->isJobInScope($payload, $params));
    }

    private function getJobSkill($payload, &$params)
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

    private function getGroup($payload, &$params)
    {
        return ($this->isGroupInScope($payload, $params));
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

    private function getStudent($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params, "student_unit"));
    }

    private function getUnit($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function getUnitStudent($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function getStudentSkillTotal($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getUnitJob($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function getUnitGoal($payload, &$params)
    {
        return ($this->isUnitInScope($payload, $params));
    }

    private function getPromotionHistory($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function getPromotionUnit($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function getUnitCompleted($payload, &$params)
    {
        return ($this->areUnitsInScope($payload, $params));
    }

    private function getUnitHistory($payload, &$params)
    {
        return ($this->isStudentInScope($payload, $params));
    }

    private function postFollowup($payload, &$params)
    {
        $params['type'] = "PEDA";
        return ($this->isStudentInScope($payload, $params));
    }
}
