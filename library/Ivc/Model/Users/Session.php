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
 * personal information.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Model
 * @subpackage	Users
 */
class Ivc_Model_Users_Session
{
    private static $_user = null;
    private $_session = null;
    private $_allowedValues = null;

    private function __construct()
    {
        $this->_session = new Zend_Session_Namespace(__CLASS__);
        $this->_allowedValues = array('locale' => null, 
                                      'currency',
                                      'timezone',
                                      'requestUri');
    }
    
    static public function namespaceUnset()
    {
        Zend_Session::namespaceUnset(__CLASS__);        
    }

    static public function getInstance()
    {
        if (null === self::$_user) {
            self::$_user = new self();
        }
        return self::$_user;
    }

    public function __set($name, $value)
    {
        if (! in_array($name, $this->_allowedValues)) {
            return null;
        }
        $this->_session->$name = $value;
    }

    public function __get($name)
    {
        if (! in_array($name, $this->_allowedValues)) {
            return null;
        }
        return $this->_session->$name;
    }
    
    public function __isset($name) {
        return isset($this->_allowedValues[$name]);
    }
}