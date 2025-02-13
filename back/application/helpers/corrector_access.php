<?php

class Corrector_Access
{
    private $controller;

    public function __construct(&$controller)
    {
        $this->controller = &$controller;
    }

    public function Access($call, $payload, &$params)
    {
        $granted = [
        ];
        
        $limited = [
            'getGroup',
            'getGroupReview',
            'putGroupReview',

            'getJob',
            'getJobReady',     
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

    // GET
    private function getJob($payload, &$params)
    {
        $params['unit_id'] = $payload['scope'];

        return (true);
    }

    private function getJobReady($payload, &$params)
    {
        $params['job_unit_id'] = $payload['scope'];

        return (true);
    }

    private function getGroup($payload, &$params)
    {
        if (!array_key_exists('group_id', $params)
            || gettype($params['group_id']) != "string"
            || strlen($params['group_id']) <= 0)
        {
            return (true);
        }

        $regparams = [
            'group_id' => $params['group_id'],
            'job_unit_id' => $payload['scope']
        ];
        $this->controller->load->model('Registration_Model');
        $reg = $this->controller->Registration_Model->getRegistration($regparams);
        if (count($reg) >= 1)
        {
            return (true);
        }
        
        return (false);
    }

    private function getGroupReview($payload, &$params)
    {
        return ($this->getGroup($payload, $params));
    }

    // private function getCalendarHistory($payload, &$params)
    // {
    //     $params['unit_id'] = $payload['scope'];

    //     return (true);
    // }

    // POST



    // PUT
    private function putGroupReview($payload, &$params)
    {
        return ($this->getGroup($payload, $params));
    }

    // DELETE
}

?>