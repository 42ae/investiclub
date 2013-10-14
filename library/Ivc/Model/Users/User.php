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
 * Object that represents a user. We can get from it his
 * personal information and settings.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Model
 * @subpackage	Users
 */
class Ivc_Model_Users_User implements Zend_Auth_Adapter_Interface, Zend_Acl_Role_Interface
{
    protected $_gateway;
    protected $_roleId;

    protected $_settings = null;
    protected $_member = null;
    protected $_club = null;
    protected $_hasClub;
    
    protected $_data = array(
        'user_id'       => null,
        'email'         => null,
        'password'      => null, 
        'first_name'    => null, 
        'last_name'     => null,
        'gender'        => null,
        'address'       => null,
        'city'          => null,
        'country'       => null,
    	'postal_code'   => null, 
        'phone_mobile'  => null,
        'phone_home'    => null,
        'date_of_birth' => null,
    	'occupation'    => null,
        'active'        => false,
        'created_on'    => null,
        'last_login'    => null,
        'last_update'   => null);
    
    public function __construct($data, $gateway)
    {
        $this->setGateway($gateway);
        $this->populate($data);

        if (!isset($this->user_id)) {
            if (!isset($this->email)) {
                if (!isset($this->first_name, $this->last_name))
                    throw new Ivc_Exception('Initial data must contain an email or a user id or a pair firstname/lastname');
            }
        }
    }
    
    public function hasClub()
    {
        if (null !== $this->_hasClub) {
            return $this->_hasClub;
        }
        $this->_hasClub = (bool) $this->getGateway()->fetchClubByUserId($this->user_id);
        return $this->_hasClub;
    }
    
    public function addToClub($club, $data)
    {
        $gateway = new Ivc_Model_Clubs_Gateway();
        return $gateway->createMember($data, $this, $club);
    }
    
    public function getFullName() 
    {
        return $this->first_name . ' ' . $this->last_name; 
    }
    
    public function sendMessage($to, $subject, $message, $namespace = 'general')
    {
        $data = array('sender_id' => $this->user_id,
                      'recipient_id' => $to,
                      'namespace' => $namespace,
                      'subject' => $subject,
                      'message' => $message,
        );
        
        $message = new Ivc_Model_Users_Messages($data, $this->getGateway());
        return $message->save();
    }

    public function getNotifications() 
    {
        return $this->getGateway()->fetchNotificationsByRecipientId($this->user_id);
    }

    public function getNotificationsUnread() 
    {
        return $this->getGateway()->fetchNotificationsUnreadByRecipientId($this->user_id);
    }

    public function getNotificationById($notificationId) 
    {
        return $this->getGateway()->fetchNotificationById($this->user_id, $notificationId);
    }

    public function getNotificationsFrom($senderId) 
    {
        return $this->getGateway()->fetchNotificationsByRecipientIdAndSenderId($this->user_id, $senderId);
    }
    
//    public function setMember($data)
//    {
//        $gateway = new Ivc_Model_Clubs_Gateway();
//        $this->_member = $gateway->createMember($data);
//    }
//
//    public function getMember($status = 'active')
//    {
//        if (null === $this->getClub()) {
//            return null;
//        }
//
//        if (null === $this->_member) {
//            $gateway = new Ivc_Model_Clubs_Gateway();
//            $this->_member = $gateway->fetchMember($this, $status);
//        }
//        
//        if (is_string($status)) {
//            $status = array($status);
//        }
//
//        foreach ($status as $state) {
//            if ($this->_member->status == $state) {
//                return $this->_member;
//            }
//        }
//        return null;
//    }
//    
//    public function setClub($data)
//    {
//        $gateway = new Ivc_Model_Clubs_Gateway();
//        $this->_club = $gateway->createClub($data);
//    }
//    
//    public function getClub($active = true)
//    {
//        if ($this->_club === null) {
//            $this->_club = $this->getGateway()->fetchClubByUserId($this->user_id, $active);
//        }
//        return $this->_club;
//    }
//
//    public function getClubId()
//    {
//        $club = $this->getClub();
//        if (null !== $club) {
//            return $club->club_id;
//        }
//        return null;
//    }

    public function getSettings()
    {
        if ($this->_settings === null) {
            $this->_settings = $this->getGateway()->fetchSettings($this->user_id);
        }
        return $this->_settings;
    }
    
    public function save()
    {
        $gateway = $this->getGateway();
        $dbTable = $gateway->getDbTable();
        $row = $dbTable->find($this->user_id)->current();
        if ($row) {
            foreach ($this->_data as $key => $value) {
                $row->$key = $value;
            }
            $row->save();
        } else {
            $this->user_id = $dbTable->insert($this->_data);
        }
        return $this;
    }
    
    public function getRoleId()
    {
        if (null === $this->_roleId)
            $this->_roleId = $this->user_id;
        return $this->_roleId;
    }
    
    public function authenticate()
    {
        $gateway = $this->getGateway();
        $table   = $gateway->getDbTable();
        $select  = $table->select();
        $select->where('email = ?', $this->email)
               ->where('password = ?', $this->password)
               ->where('active = ?', true);
        $user = $table->fetchRow($select);
        if ($user === null) {
            $result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE_UNCATEGORIZED, null);
        } else {
            $user->last_login = Zend_Date::now()->toString(Zend_Date::ISO_8601);
            $user->save();
            $this->populate($user);
            unset($this->password);
            $result = new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $this);
        }
        return $result;
    }

    public function setGateway(Ivc_Model_Users_Gateway $gateway)
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