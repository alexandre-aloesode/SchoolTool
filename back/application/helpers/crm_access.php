<?php

class CRM_Access
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
            'postAbsence',
            'putAbsence',
            // 'deleteAbsence',

            'getActivity',

            'getActivityAttendance',

            'getAlternance',
            'putAlternance',
            'postAlternance',
            
            'getAlert',
            'putAlert',

            'getApplicant',
            'postApplicant',
            'putApplicant',
            'deleteApplicant',

            'getCalendar',

            'getCalendarDay',
            'postCalendarDay',
            'putCalendarDay',
            'deleteCalendarDay',

            'getCalendarHistory',

            'getFollowup',
            'getStudentFollowup',

            'postInvoice',
            'getInvoiceCount',

            'getLogtime',
            'getLastLog',

            'getStudentLastLogtime',
            
            'getLogtime_Event',
            'postLogtime_Event',

            //'DeleteFollowup',
            'getPromotion',
            'putPromotion',

            'getSection',

            'getStatus',
            'getSituations',
            'getStudies',

            'getStudent', 
            'putStudent',
            'postNewBadges',  

            'getUnit',

            'getStudentAttendance',
            'getGroupAttendance',
        ];

        $limited = [

            'postFollowup',
            'postStudentFollowup'
        ];

        if (in_array($call, $granted))
        {
            return (true);
        }

        if (in_array($call, $limited))
        {
            return ($this->$call($payload, $params));
        }

        return (false);
    }

    // POST
    private function postFollowup($payload, &$params)
    {
        $params['type'] = "ADM";

        return (true);
    }

    private function postStudentFollowup($payload, &$params)
    {
        $params['type'] = "ADM";

        return (true);
    }

    // PUT
private function putFollowup($payload, &$params)
    {
        $params['type'] = "ADM";

        return (true);
    }
    // DELETE
}

?>