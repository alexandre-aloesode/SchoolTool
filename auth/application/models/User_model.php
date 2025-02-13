<?php

class User_model extends CI_Model
{
    private $table = 'user';

    public function __construct() {
		  $this->load->database();
    }

    public function GetUser($data) {

        return $this->db->select($this->table.'.id as id, email, role.name as role', false)
        ->join('role', $this->table.'.role_id = role.id', 'left')
        ->where($data)
        ->get($this->table)
        ->result_array();
    }

    public function GetUserScope($data) {
      return $this->db->select('scope.user_id as user_id, scope.scope_value as scope_value', false)
      ->where($data)
      ->get('scope')
      ->result_array();
    }

    public function GenerateSecret($data) {
      return $this->db->insert("secret", $data);
    }


    public function GetUserToken($secret) {
      return $this->db->select('secret, user_id, issue_date, status', false)
      ->where(array('secret' => $secret))
      ->get('secret')
      ->result_array();
    }
}

?>