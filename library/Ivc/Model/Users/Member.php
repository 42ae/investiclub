<?php
/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category	Ivc
 * @package		Ivc_Model
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Handle users and clubs relationship
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Model
 * @subpackage	Clubs
 */
class Ivc_Model_Users_Member
{
    protected $_gateway;
    protected $_user;
    protected $_club;
    
    protected $_data = array(
        'member_id'        => null,
        'club_id'          => null,
        'user_id'          => null,
        'role'             => null, 
        'admin'            => null,
        'enrollement_date' => null,
        'departure_date'   => null,
        'status'           => null,
        'pending'          => null,
        'created_on'       => null,
        'deleted_on'       => null,
        'last_update'      => null);

    public function __construct($data, Ivc_Model_Users_User $user, Ivc_Model_Clubs_Club $club, $gateway)
    {
        $this->setGateway($gateway);
        $this->populate($data);
        $this->setUser($user);
        $this->setClub($club);
        
        if (!($this->getUser() instanceof Ivc_Model_Users_User AND
            $this->getClub() instanceof Ivc_Model_Clubs_Club)) {
            throw new Exception('Initial data must contain a club and a user instance');
        }
    }
    
    public function setUser(Ivc_Model_Users_User $user)
    {
        $this->_user = $user;
    }

    public function getUser()
    {
        if ($this->_user) {
            return $this->_user;
        }
    }

    public function setClub(Ivc_Model_Clubs_Club $club)
    {
        $this->_club = $club;
    }

    public function getClub()
    {
        if ($this->_club) {
            return $this->_club;
        }
    }
    
    public function save()
    {
        $gateway = $this->getGateway();
        $dbTable = $gateway->getDbTable('Ivc_Model_Clubs_DbTable_Members');
        $row = $dbTable->find($this->member_id)->current();
        if ($row) {
            foreach ($this->_data as $key => $value) {
                $row->$key = $value;
            }
            $row->save();
        } else {
            $id = $dbTable->insert($this->_data);
            $this->member_id = $id;
        }
    }
    
    public function delete()
    {
        $memberManager = new Model_Members_Members(array('clubId' => $this->getClub()->club_id));
        if (!$memberManager->checkAcl('deleteMember')) {
            throw new Ivc_Acl_Exception;
        }
        
        $this->status = 'inactive';
        $this->deleted_on = Zend_Date::now()->toString(Zend_Date::ISO_8601);
        $this->departure_date = Zend_Date::now()->toString(Zend_Date::ISO_8601);
        $this->save();
    }
    
    public function makeAdmin(Ivc_Model_Users_Member $member)
    {
        if (!$this->isActive()) {
            throw new Ivc_Exception("Can't be set as admin", Zend_Log::ERR);
        }
        
        $memberManager = new Model_Members_Members(array('clubId' => $member->getClub()->club_id));
        if (!$memberManager->checkAcl('makeAdmin')) {
            throw new Ivc_Acl_Exception;
        }
        
        $member->admin = true;
        $this->admin = false;
        
        $member->save();
        $this->save();
    }
    
    public function edit(array $data)
    {
        $memberManager = new Model_Members_Members(array('clubId' => $this->getClub()->club_id));
        if (!$memberManager->checkAcl('editMember')) {
            throw new Ivc_Acl_Exception;
        }
        
        if ($this->isUnregistered()) {
            $this->getUser()->first_name = $data['first_name'];
            $this->getUser()->last_name = $data['last_name'];
            $this->getUser()->save();
        }

        $this->enrollement_date = $data['enrollement_date'];
        $this->role = $data['role'];
        
        $this->save();
    }
    
    public function isActive()
    {
        if ($this->getUser()->email AND $this->status == 'active')
            return true;
        return false;
    }

    public function isUnregistered()
    {
        if (null == $this->getUser()->email AND $this->status == 'active')
            return true;
        return false;
    }

    public function isPending()
    {
        if (null === $this->deleted_on AND $this->pending == true)
            return true;
        return false;
    }
    
    public function getStats()
    {
        $treasury = new Model_Treasury_Treasury();
        $stats = $treasury->getMemberStats($this->member_id);
        return $stats;
    }
    
    public function setGateway(Ivc_Model_Clubs_Gateway $gateway)
    {
        $this->_gateway = $gateway;
        return $this;
    }

    public function getGateway()
    {
        return $this->_gateway;
    }

    public function populate($data)
    {
        if ($data instanceof Zend_Db_Table_Row_Abstract) {
            $data = $data->toArray();
        } elseif (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            throw new Exception('Initial data must be an array or object');
        }
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }
    
    public function toArray() {
        return $this->_data;
    }
    
    public function __set($name, $value)
    {
        if (! array_key_exists($name, $this->_data)) {
            throw new Exception('Invalid property \"' . $name . '\"');
        }
        $this->_data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }
        return null;
    }

    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    public function __unset($name)
    {
        if (isset($this->$name)) {
            $this->_data[$name] = null;
        }
    }
}