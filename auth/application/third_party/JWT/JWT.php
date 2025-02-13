<?php

/*

SCOPE: ADMIN - STAFF - TEACHER - STUDENT ...

*/

class JWT
{
    private $secret;
    private $payload;

    public function __construct()
    {
        $this->secret = LPTF_JWT_KEY;
        $this->payload = [];
    }

    public function generate($data)
    {
        $header = [
            "alg" => "HS256",
            "typ" => "JWT"
        ];

        $header64 = $this->base64_encode_urlstyle(json_encode($header));
        $payload64 = $this->base64_encode_urlstyle(json_encode($data));
        $signature = hash_hmac('sha256', $header64 . "." . $payload64, $this->secret, true);
        $sig64 = $this->base64_encode_urlstyle($signature);

        return ($header64.".".$payload64.".".$sig64);
    }

    public function verify_token($token)
    {
        $subtoken = explode('.', $token);

        if (count($subtoken) != 3)
        {
            return (false);
        }

        $payload = $this->base64_decode_urlstyle($subtoken[1]);
        $payload = (array)json_decode($payload);

        $verif = $this->generate($payload);
        if ($verif == $token)
        {
            $this->payload = $payload;
            return (true);
        }
        else
        {
            $this->payload = [];
            return (false);
        }
    }

    public function get_payload()
    {        
        return ($this->payload);
    }

    private function base64_encode_urlstyle($data)
    {
        $urlencoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));

        return ($urlencoded);
    }

    private function base64_decode_urlstyle($data)
    {
        $urldecoded = str_replace(['-', '_', ''], ['+', '/', '='], $data);

        return (base64_decode($urldecoded));
    }
}

?>