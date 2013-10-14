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
 * User gateway
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Model
 * @subpackage	Users
 */
class Ivc_Model_Users_Gateway implements Zend_Acl_Role_Interface
{
    protected $_acl;
    protected $_dbTable;
    protected $_dbAdapter;
    
    public function getRoleId()
    {
        if (!isset($this->role)) {
            return 'guest';
        }
        return $this->role;
    }
    
    public function setAcl(Ivc_Acl $acl)
    {
        $this->_acl = $acl;
        return $this;
    }

    public function getAcl()
    {
        return $this->_acl;
    }
    
    function setDbAdapter($dbAdapter = null)
    {
        if ($dbAdapter && ($dbAdapter instanceof Zend_Db_Adapter_Abstract)) {
            $this->_dbAdapter = $dbAdapter;
        } else {
            $this->_dbAdapter = Zend_Db_Table::getDefaultAdapter();
        }
        return $this;
    }
    
    function getDbAdapter()
    {
        if (!$this->_dbAdapter) {
            $this->setDbAdapter();
        }
        return $this->_dbAdapter;
    }
    
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (! $dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable($dbTable = null)
    {
        if (null === $dbTable) {
            $this->setDbTable('Ivc_Model_Users_DbTable_Users');
            return $this->_dbTable;
        }
        
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (! $dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        return $dbTable;
    }
    
    /**
     * Creates a user and returns an instance of {@see Ivc_Model_Users_User}
     * 
     * @param mixed $data
     */    
    public function createUser($data) {
        return new Ivc_Model_Users_User($data, $this);
    }

    public function createUsers($data) {
        return new Ivc_Model_Users_Users($data, $this);
    }

    public function createSettings($data) {
        return new Ivc_Model_Users_Settings($data, $this);
    }

    public function createNotification($data) {
        return new Ivc_Model_Users_Notifications($data, $this);
    }

    /**
     * Fetch Methods
     */
    public function fetchAll()
    {
        $result = $this->getDbTable()->fetchAll();
        return new Ivc_Model_Users_Users($result, $this);
    }

    public function fetchUser($id)
    {
        $dbTable = $this->getDbTable();
        $select = $dbTable->select()->where('user_id = ?', $id);
        $result = $dbTable->fetchRow($select);
        if (null != $result) {
            $result = $this->createUser($result);
        }
        return $result;
    }

    public function fetchNotificationsByRecipientId($recipientId)
    {
        $dbTable = $this->getDbTable('Ivc_Model_Users_DbTable_Notifications');
        $select = $dbTable->select()->where('recipient_id = ?', $recipientId)
                                    ->where('deleted = ?', false);
        $result = $dbTable->fetchAll($select);
        return $result;
    }

    public function fetchNotificationsUnreadByRecipientId($recipientId)
    {
        $dbTable = $this->getDbTable('Ivc_Model_Users_DbTable_Notifications');
        $select = $dbTable->select()->where('recipient_id = ?', $recipientId)
                                    ->where('is_read = ?', false)
                                    ->where('deleted = ?', false);
        $result = $dbTable->fetchAll($select);
        return $result;
    }

    public function fetchNotificationById($recipientId, $notificationId)
    {
        $dbTable = $this->getDbTable('Ivc_Model_Users_DbTable_Notifications');
        $select = $dbTable->select()->where('notification_id = ?', $notificationId)
                                    ->where('recipient_id = ?', $recipientId)
                                    ->where('deleted = ?', false);
        $result = $dbTable->fetchRow($select);
        return $result;
    }

    public function fetchNotificationsByRecipientIdAndSenderId($recipientId, $senderId)
    {
        $dbTable = $this->getDbTable('Ivc_Model_Users_DbTable_Notifications');
        $select = $dbTable->select()->where('recipient_id = ?', $recipientId)
                                    ->where('sender_id = ?', $senderId)
                                    ->where('deleted = ?', false);
        $result = $dbTable->fetchAll($select);
        return $result;
    }

    public function fetchAllSentAndReceivedMessagesByUserId($userId, $namespace)
    {
        $dbTable = $this->getDbTable('Ivc_Model_Users_DbTable_Messages');
        $select = $dbTable->select()->where('namespace = ?', $namespace)
                                    ->where('deleted = ?', false)
                                    ->where('recipient_id = ?', $userId)
                                    ->orWhere('sender_id = ?', $userId);
        $result = $dbTable->fetchAll($select);
        return $result->toArray();
    }

    public function fetchUserByMemberId($memberId)
    {
        $dbTable = $this->getDbTable('Ivc_Model_Clubs_DbTable_Members');
        $where = $dbTable->select()->where('member_id = ?', $memberId);
        $member = $dbTable->fetchRow($where);
        
        if (null !== $member) {
            $user = $member->findParentRow('Ivc_Model_Users_DbTable_Users', 'Users');
            $user = $this->createUser($user);
            return $user;
        }
        return null;
        
    }
    
    /**
     * Fetch user's settings
     * 
     * @param int $id
     * @return null|Ivc_Model_Users_Settings
     */
    public function fetchSettings($id)
    {
        $dbTable = $this->getDbTable();
        $select = $dbTable->select()->where('user_id = ?', $id);
        $user = $dbTable->fetchRow($select);
        
        if (null !== $user) {
            $dbTable = $this->getDbTable('Ivc_Model_Users_DbTable_UserSettings');
            $select = $dbTable->select()->where('user_id = ?', $id);
            $settings = $dbTable->fetchRow($select);
            
            if (null != $settings) {
                $result = $this->createSettings($settings);
            } else {
                $result = $this->createSettings(array('user_id' => $id));
                $result->save();
            }
            return $result;
         }
    }
    
    public function fetchClubByUserId($userId)
    {
        $dbTable = $this->getDbTable('Ivc_Model_Clubs_DbTable_Members');
        $where = $dbTable->select()->where('user_id = ?', $userId)
                                   ->where('status = ?', 'active');
        $user = $dbTable->fetchRow($where);

        if (null !== $user) {
            // @todo: implements case if one user has many clubs (return rowset and clubs obj)
            $dbTable = $this->getDbTable('Ivc_Model_Clubs_DbTable_Clubs');
            $where = $dbTable->select()->where('active IN (?)', true);
            $club = $user->findParentRow('Ivc_Model_Clubs_DbTable_Clubs', 'Clubs', $where);
            if ($club != null) {
                $gateway = new Ivc_Model_Clubs_Gateway();
                $result = $gateway->createClub($club);
                return $result;
            }
        }
        return null;
    }
    
    public function fetchByEmail($email)
    {
        $dbTable = $this->getDbTable();
        $select = $dbTable->select();
        $select->where('email = ?', $email);
        
        $user = $dbTable->fetchRow($select);
        if (null != $user) {
            $user = $this->createUser($user);
        }
        return $user;
    }
}