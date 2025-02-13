<?php

/*

    PAYLOAD MODEL
    {
        user_id = 123,
        user_email = abc,
        exp = 123,
        role = admin | student | crm
    }

*/

class Role_Helper
{

    public function __construct(&$controler = null)
    {
        $this->controler = &$controler;
    }

    public function Access(&$params)
    {
        $role = $this->controler->token_helper->get_payload()['role'];
        $call = debug_backtrace()[1]['function'];
        $scope = $this->controler->token_helper->get_payload()['scope'];
        $tab_role = $this->getRoleFromPayload($role);

        if ($role == 'admin') {
            return (true);
        }
        else {
            include APPPATH . 'helpers/' . $tab_role['name'] . '_access.php';
            $role_access = new $tab_role['access']($this->controler);
            return ($role_access->Access($call, $this->controler->token_helper->get_payload(), $params));
        }
        return (false);
    }

    public function getRoleFromPayload($role)
    {
        foreach (TAB_ROLES as $key => $value) {
            if ($value['name'] == $role) {
                return ($value);
            }
        }
        return false;
    }

    public function getRoleNameFromId($id)
    {
        foreach (TAB_ROLES as $key => $value) {
            if ($value['id'] == $id) {
                return ($value['name']);
            }
        }
        return false;
    }

    public function getRoleIdFromName($name)
    {
        foreach (TAB_ROLES as $key => $value) {
            if ($value['name'] == $name) {
                return ($value['id']);
            }
        }
        return false;
    }
}
