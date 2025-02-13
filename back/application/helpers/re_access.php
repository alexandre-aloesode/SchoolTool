<?php

class Re_Access
{
    private $controller;

    public function __construct(&$controller)
    {
        $this->controller = &$controller;
    }

    public function Access($call, $payload, &$params)
    {
        $granted = [
            'getAbsence',

            'getActivity',

            'getActivityAttendance',

            'getAlternance',
            'putAlternance',
            'postAlternance',

            'getAlert',

            'getApplicant',

            'getCalendar',

            'getCalendarDay',

            'getCalendarHistory',

            'getClass',

            'getFollowup',

            'postInvoice',
            'getInvoiceCount',

            'getJob',

            'getJobSkill',

            'getStudentJobAvailable',

            'getJobStudentAvailable',

            'getJobAwait',

            'getJobProgress',

            'getJobReady',

            'getJobChecked',

            'getJobDone',

            'getLogtime',
            'getLastLog',
            'getPromotionLogtime',
            'getStudentLastLogtime',
            'getPromotionLastLogtime',
            'getPromotionLogtimeAverage',
            'getLogtime_Event',

            'getPromotion',

            'getPromotionHistory',

            'getPromotionUnit',

            'getRegistration',

            'getJobStudent',

            'getGroup',            
            'getGroupReview',

            'getGroupAvailable',

            'getSection',

            'getStudent',
            'getStudentActivity',
            'getStudentInactive',
            'getSkill',
            'getStudentUnit',
            'getStudentSkill',
            'getStudentSkillTotal',
            'getStudentClassTotal',
            'getStudentPromotionClassTotal',

            'getPromotionClassTotal',

            'getUnit',
            'getUnitJob',
            'getUnitCompleted',
            'getUnitGoal',
            'getUnitGoalStudent',
            'getUnitHistory',
            'getUnitStudent',

            'getWaitingList',

            'getGroupAttendance',
            'getStudentAttendance',
        ];

        $limited = [
            'postFollowup',
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

    // POST

private function postFollowup($payload, &$params)
    {
        $params['type'] = "RE";
        return (true);
    }

    // PUT

    // DELETE
}
