<?php
/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category	InvestiClub
 * @package		Model
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Create model
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Clubs
 */
class Model_Clubs_Create
{
    protected $_data = array();
    protected $_user = array();

    /**
     * Club constructor
     * 
     * Formats a club creation request and send the broker to the mapper
     * @param array $data
     */
    public function __construct($data)
    {
        $this->_user['role'] = $data['role'];

        if (! isset($data['broker_id']) OR !$data['broker_id']) {
            $broker = new Model_Clubs_Brokers(array('name' => $data['broker']));
            $mapper = new Model_Clubs_BrokersMapper();
            $data['broker_id'] = $mapper->save($broker);
        }
        unset($data['broker']);
        unset($data['role']);

        $data['active'] = true;
        $data['created_on'] = Zend_Date::now()->toString(Zend_Date::ISO_8601);
        $this->_data = $data;
    }

    /**
     * Creates a new club and save it in the database. This methods also
     * adds the club creator in the club as an administrator.
     */
    public function newClub()
    {
        $gateway = new Ivc_Model_Clubs_Gateway();
        $club = $gateway->createClub($this->_data);
        $id = $club->save();
        
        $this->_user['user_id'] = Ivc::getCurrentUserId();
        $this->_user['club_id'] = $id;
        $this->_user['enrollement_date'] = $this->_data['registration_date'];
        $this->_user['admin'] = true;
        $this->_user['active'] = true;
        $this->_user['pending'] = false;
        $club->addMember($this->_user, Ivc::getCurrentUser());
        
    }
}