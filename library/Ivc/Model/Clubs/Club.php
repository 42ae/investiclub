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
 * Object that represents a club.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Model
 * @subpackage	Clubs
 */
class Ivc_Model_Clubs_Club
{
    protected $_gateway;
    protected $_settings = null;
    protected $_members = null;
       
    protected $_data = array(
        'club_id'                 => null,
        'name'                    => null,
        'broker_id'               => null, 
        'country'                 => null, 
        'currency'                => null,
        'registration_date'  => null,
        'dissolution_date'   => null,
    	'active'                  => null,
        'created_on'              => null,
        'last_update'             => null);
    
    public function __construct($data, $gateway)
    {
        $this->setGateway($gateway);       
        $this->populate($data);
        
        if (!isset($this->club_id) AND !isset($this->name)) {
            throw new Exception('Initial data must at least contain a name or a club id');
        }
    }
    
    public function getMembers($where = array('status'     => array('active'),
    									      'pending'    => array(true, false)), 
    						                   $deleted_on = 'IS NULL')
    {
        $this->_members = $this->getGateway()->fetchMembers($this, $where, $deleted_on);
        return $this->_members;
    }
    
    public function getMember($userId)
    {
        return $this->getGateway()->fetchMember(array('club_id' => $this->club_id, 
        										      'user_id' => $userId));
    }

    public function addMember($data, Ivc_Model_Users_User $user)
    {
        return $this->getGateway()->createMember($data, $user, $this);
    }
    
    /**
     * @return Ivc_Model_Clubs_Settings
     */
    public function getSettings()
    {
        if ($this->_settings === null) {
            $this->_settings = $this->getGateway()->fetchSettings($this->club_id);
        }
        return $this->_settings;
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

    public function save()
    {
        $gateway = $this->getGateway();
        $dbTable = $gateway->getDbTable();
        $row = $dbTable->find($this->club_id)->current();
        if ($row) {
            foreach ($this->_data as $key => $value) {
                $row->$key = $value;
            }
            $row->save();
        } else {
            $this->club_id = $dbTable->insert($this->_data);
        }
    }

    public function saveSettings()
    {
        $gateway = $this->getGateway();
        $gateway->setDbTable('Ivc_Model_Clubs_DbTable_ClubSettings');
        $dbTable = $gateway->getDbTable();
        $row = $dbTable->find($this->club_id)->current();
        if ($row) {
            foreach ($this->_settings as $key => $value) {
                $result->$key = $value;
            }
            $row->save();
        } else {
            $dbTable->insert($this->_settings);
        }
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