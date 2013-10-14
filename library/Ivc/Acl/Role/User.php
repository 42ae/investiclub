<?php
interface Ivc_Acl_Role_User extends Zend_Acl_Role_Interface
{
    public function getRoleId()
    {
        return $this->user_id;
    }
}