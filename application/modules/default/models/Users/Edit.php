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
 * Edit user model
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Users
 */
class Model_Users_Edit
{
    /**
     * User's form information aray
     * @var array
     */
    protected $_data = array();

    /**
     * User's object
     * @var Ivc_Model_Users_User
     */
    protected $_user;

    /**
     * User's settings array
     * @var array
     */
    protected $_settings = array();

    /**
     * Class constructor
     *
     * Receives an array of data to edit in the current user's
     * profile.
     * 
     * @param	array $data
     * @see		AccountController
     */
    public function __construct($data)
    {
        unset($data['current_password']);
        unset($data['confirm_password']);
        unset($data['password']);
        unset($data['token']);
        
        $this->_data = $data;    
        $this->_user = Ivc::getCurrentUser();
    }

    /**
     * Merge an international calling code with a phone number
     */
    public function mergePhoneNumber()
    {
        if (array_key_exists('phone_home_code', $this->_data) AND array_key_exists('phone_home', $this->_data)) {    
            $this->_data['phone_home'] = $this->_data['phone_home_code'] . $this->_data['phone_home'];
        }
        if (array_key_exists('phone_mobile_code', $this->_data) AND array_key_exists('phone_mobile', $this->_data)) {
            $this->_data['phone_mobile'] = $this->_data['phone_mobile_code'] . $this->_data['phone_mobile'];
        }

        unset($this->_data['phone_home_code']);
        unset($this->_data['phone_mobile_code']);
    }
    
    /**
     * Process user's modification
     * 
     * Populates a user and updates his new profile into the database.
     * Return the last insert id (user's id).
     * 
     * @return $id
     */
    public function process()
    {
        $this->_user->populate($this->_data);
        $id = $this->_user->save();
        return $id;
    }
}