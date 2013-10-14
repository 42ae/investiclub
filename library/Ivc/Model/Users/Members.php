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
 * Object that represents a bunch of users.
 * This class extends both Iterator and Countable pattern.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Model
 * @subpackage	Users
 */
class Ivc_Model_Users_Members implements Countable, Iterator
{
    protected $_count;
    protected $_gateway;
    protected $_resultSet = null;

    public function __construct($results, $users, $club, $gateway)
    {
        $this->setGateway($gateway);
        $this->populate($results, $users, $club);
        $this->_count = null;
    }
    
    /**
     * 
     * @return mixed Returns null or Ivc_Model_Users_User
     */
    public function getUserByMemberId($memberId)
    {
        foreach ($this->_resultSet as $member) {
            if ($member->member_id == $memberId) {
                return $member->getUser();
            }
        }
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

    public function count()
    {
        if (null === $this->_count) {
            $this->_count = count($this->_resultSet);
        }
        return $this->_count;
    }
    
    public function current()
    {
        return current($this->_resultSet);
    }

    public function key()
    {
        
        return key($this->_resultSet);
    }

    public function next()
    {
        return next($this->_resultSet);
    }

    public function rewind()
    {
        return reset($this->_resultSet);
    }

    public function valid()
    {
        return (bool) $this->current();
    }
    
    public function populate($members, $users, $club)
    {
        $gateway = $this->getGateway();
        if ($members instanceof Zend_Db_Table_Rowset) {
            foreach ($members as $member) {
                foreach ($users as $user) {
                    if ($member->user_id == $user->user_id) {
                        $this->_resultSet[$member->member_id] = $gateway->createMember($member, $user, $club);
                    }
                }
            }
        } else if ($members instanceof Zend_Db_Table_Row) {
            $this->_resultSet[$members->member_id] = $gateway->createMember($members, $users->current(), $club);
        }
    }
}