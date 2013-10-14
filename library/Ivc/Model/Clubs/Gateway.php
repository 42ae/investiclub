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
 * Club gateway
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Model
 * @subpackage	Clubs
 */
class Ivc_Model_Clubs_Gateway
{
    protected $_dbTable;
    protected $_dbAdapter;
    
    public function setDbAdapter($dbAdapter = null)
    {
        if ($dbAdapter && ($dbAdapter instanceof Zend_Db_Adapter_Abstract)) {
            $this->_dbAdapter = $dbAdapter;
        } else {
            $this->_dbAdapter = Zend_Db_Table::getDefaultAdapter();
        }
        return $this;
    }
    
    public function getDbAdapter()
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
        if (null !== $dbTable) {
            if (is_string($dbTable)) {
                $dbTable = new $dbTable();
            }
            if (! $dbTable instanceof Zend_Db_Table_Abstract) {
                throw new Exception('Invalid table data gateway provided');
            }
            return $dbTable;
        }
        
        if (null === $this->_dbTable) {
            $this->setDbTable('Ivc_Model_Clubs_DbTable_Clubs');
        }
        return $this->_dbTable;
    }
    
    public function createClub($data) {
        return new Ivc_Model_Clubs_Club($data, $this);
    }
    
    public function createSettings($data) {
        return new Ivc_Model_Clubs_Settings($data, $this);
    }

    public function createMember($data, $user, $club) {
        return new Ivc_Model_Users_Member($data, $user, $club, $this);
    }

    public function createMembers($data, $users, $club) {
        return new Ivc_Model_Users_Members($data, $users, $club, $this);
    }
    
    public function fetchAll()
    {
        $result = $this->getDbTable()->fetchAll();
        return new Ivc_Model_Clubs_Clubs($result, $this);
    }

    public function fetchMembers(Ivc_Model_Clubs_Club $club, $where, $deleted_on)
    {
        $dbTable = $this->getDbTable();
        $clubRow = $dbTable->find($club->club_id)->current();
        
        $dbTable = $this->getDbTable('Ivc_Model_Clubs_DbTable_Members');
        $select = $dbTable->select()->where('status IN (?)',  $where['status'])
                                    ->where('pending IN (?)', $where['pending'])
                                    ->where('deleted_on ' . $deleted_on);
        
        // grab users in club
        $users = $clubRow->findManyToManyRowset('Ivc_Model_Users_DbTable_Users',
        									    'Ivc_Model_Clubs_DbTable_Members',
                                                null, null, $select);
                                                
        $select = $this->getDbTable('Ivc_Model_Users_DbTable_Users')
                       ->select()->where('status IN (?)',  $where['status'])
                       ->where('pending IN (?)', $where['pending'])
                       ->where('deleted_on ' . $deleted_on);
        
        $results = $clubRow->findDependentRowset('Ivc_Model_Clubs_DbTable_Members', null, $select);
        
        if ($results->count()) {
            $gateway = new Ivc_Model_Users_Gateway();
            $users = $gateway->createUsers($users);
            if ($users) {
                return $this->createMembers($results, $users, $club);
            }
        }
        return $results;
    }

    public function fetchActiveMembersByDate(Ivc_Model_Clubs_Club $club, $date)
    {
    	$dbTable = $this->getDbTable();
        $clubRow = $dbTable->find($club->club_id)->current();
        
        $dbTable = $this->getDbTable('Ivc_Model_Clubs_DbTable_Members'); 
        $select = $dbTable->select()->where('enrollement_date <= ?', $date)
        							->where('status IN (?)', 'active')
        							->orWhere('status IN (?)', 'inactive')
        							->where('departure_date >= ?', $date);
        // grab users in club
        $users = $clubRow->findManyToManyRowset('Ivc_Model_Users_DbTable_Users',
        									    'Ivc_Model_Clubs_DbTable_Members',
                                                null, null, $select);
        
        $results = $clubRow->findDependentRowset('Ivc_Model_Clubs_DbTable_Members');
		//Zend_Debug::dump($results);
        if ($results) {
            $gateway = new Ivc_Model_Users_Gateway();
            $users = $gateway->createUsers($users);
            return $this->createMembers($results, $users, $club);
        }
    }
    
    public function fetchMember(Ivc_Model_Users_User $user)
    {
        $table = $this->getDbTable('Ivc_Model_Clubs_DbTable_Members');
        $where = $table->select()->where('user_id  = ?',  $user->user_id);
        
        $result = $table->fetchRow($where);

        if (null !== $result) {
            $table = $this->getDbTable();
            $where = $table->select()->where('club_id  = ?',  $result->club_id);
            $clubRow = $table->fetchRow($where);
            $club = $this->createClub($clubRow);
            
            $member = $this->createMember($result, $user, $club);
            return $member;
        }
    }

    public function fetchWaitingForApprovalMember(Ivc_Model_Users_User $user)
    {
        $table = $this->getDbTable('Ivc_Model_Clubs_DbTable_Members');
        $where = $table->select()->where('user_id  = ?',  $user->user_id)
                                 ->where('pending  = ?',  true)
                                 ->where('status  = ?',  'inactive');
        
        $result = $table->fetchRow($where);
        if (null !== $result) {
            $table = $this->getDbTable();
            $where = $table->select()->where('club_id  = ?',  $result->club_id);
            $clubRow = $table->fetchRow($where);
            $club = $this->createClub($clubRow);
            
            $member = $this->createMember($result, $user, $club);
            return $member;
        }
    }

    /**
     * @param Ivc_Model_Users_Member
     */
    public function fetchMemberById($id)
    {
        $table = $this->getDbTable('Ivc_Model_Clubs_DbTable_Members');
        $where = $table->select()->where('member_id  = ?',  $id);
        
        $result = $table->fetchRow($where);

        if (null !== $result) {
            // fetch member's club
            $where = $this->getDbTable()->select()->where('club_id  = ?',  $result->club_id);
            $clubRow = $this->getDbTable()->fetchRow($where);
            $club = $this->createClub($clubRow);

            // fetch member's user data
            $table = $this->getDbTable('Ivc_Model_Users_DbTable_Users');
            $where = $table->select()->where('user_id  = ?',  $result->user_id);
            $userRow = $this->getDbTable()->fetchRow($where);
            $gateway = new Ivc_Model_Users_Gateway();
            $user = $gateway->createUser($userRow);
            
            $result = $this->createMember($result, $user, $club);
        }
        return $result;
    }
    
    /**
     * Fetch a club's settings
     * 
     * @param int $id
     * @return null|Ivc_Model_Users_Settings
     */
    public function fetchSettings($id)
    {
        $dbTable = $this->getDbTable();
        $select = $dbTable->select()->where('club_id = ?', $id);
        $club = $dbTable->fetchRow($select);
        
        if (null !== $club) {
            $dbTable = $this->getDbTable('Ivc_Model_Clubs_DbTable_ClubSettings');
            $select = $dbTable->select()->where('club_id = ?', $id);
            $settings = $dbTable->fetchRow($select);
            
            if (null != $settings) {
                $result = $this->createSettings($settings);
            } else {
                $result = $this->createSettings(array('club_id' => $id));
                $result->save();
            }
            return $result;
         }
    }

    public function fetchClubById($id)
    {
        $dbTable = $this->getDbTable();
        $select = $dbTable->select()->where('club_id = ?', $id)
                                    ->where('active = ?', true);
        $result = $dbTable->fetchRow($select);
        if (null !== $result) {
            $result = $this->createClub($result);
        }
        return $result;
    }

    public function fetchAdminByClubId($id)
    {
        $dbTable = $this->getDbTable('Ivc_Model_Clubs_DbTable_Members');
        $select = $dbTable->select()->where('club_id = ?', $id)
                                    ->where('admin = ?', true)
                                    ->where('status = ?', 'active');
        $result = $dbTable->fetchRow($select);
        if (null !== $result) {
            $result = $this->fetchMemberById($result->member_id);
        }
        return $result;
    }

    public function fetchClub($clubId)
    {
        $dbTable = $this->getDbTable();
        $result = $dbTable->find($clubId)->current();
        if (null !== $result) {
            $result = $this->createClub($result);
        }
        return $result;
    }
    
    public function fetchClubBySearchString($searchString)
    {
        $dbTable = $this->getDbTable();
        $select = $dbTable->select()->where("name LIKE '%$searchString%'")
                                    ->where('active = ?', true);
        $result = $dbTable->fetchAll($select);
        return $result;
    }
}