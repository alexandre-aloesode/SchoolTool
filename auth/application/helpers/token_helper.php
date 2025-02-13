<?php

/*

    PAYLOAD MODEL
    {
        user_id = 123,
        user_email = abc,
        scope = admin | student
    }

*/

class Token_Helper
{

    public function __construct(&$controler = null)
    {
        $this->controler = &$controler;
    }

	public function verify_token() {
         // en prod:
        //$allheaders = getallheaders();

        // en dev:
        // $allheaders['Token'] = $this->controller->jwt->generate(
        //     [
        //         "scope" => ["710"], // role student: doit contenir id du student
        //         // "scope" => ["126", "125", "131"], // role peda ou teacher: doit contenir les id des units du peda/teacher
        //         "user_email" => "your.email@laplateforme.io",
        //         "role" => 'admin',
        //         "exp" => "1610800000000",
        //     ]
        // );
        // if (isset($allheaders["Token"])) {
        //     $token = $allheaders["Token"];
        //     if (!$this->controller->jwt->verify_token($token))
        //         return (false);
        //     $this->payload = $this->controller->jwt->get_payload();
        //     return (true);
        // } else {
        //     return (false);
        // }
	    $allheaders = getallheaders();
        if (isset($allheaders["Token"])) {
            $token = $allheaders["Token"];
			if (!$this->controler->jwt->verify_token($token)) {
                return (false);
            }
            $this->payload = $this->controler->jwt->get_payload();
            return (true);
        }
        else {
            return (false);
        }
    }

    public function token_valid()
    {
        if (!isset($this->payload["exp"]) || ($this->payload["exp"] < time()))
            return (false);
        return (true);
    }

    public function get_payload() {
        return ($this->payload);
    }
}

?>