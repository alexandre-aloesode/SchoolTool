<?php

class Status_Helper
{
    private $status;

    public function __construct()
    {
        $this->status = 200;
    }

    public function GetStatus()
    {
        return ($this->status);
    }

    public function IsValid()
    {
        if ($this->status == 200)
        {
            return (true);
        }

        return (false);
    }

    public function BadMethod()
    {
        $this->status = 405;

        return ($this->Error());
    }

    public function Denied()
    {
        $this->status = 401;

        return ($this->Error());
    }

    public function PreconditionFailed()
    {
        $this->status = 412;

        return ($this->Error());
    }

    public function NoContent()
    {
        $this->status = 204;

        return ($this->Error());
    }

    public function Forbidden()
    {
        $this->status = 403;

        return ($this->Error());
    }

    public function ExpectationFailed()
    {
        $this->status = 417;

        return ($this->Error());
    }

    public function TokenExpired()
    {
        $this->status = 402; // 498 non supporte par Apache

        return ($this->Error());
    }

    public function Error()
    {
        http_response_code($this->status);

        return ([]);
    }
}

?>