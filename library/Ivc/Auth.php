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
 * @package		Ivc_Auth
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * 
 * Auth is used to make a link between a authentification request and
 * the user's object.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Auth
 */
class Ivc_Auth
{
    
    const FAILURE_UNCATEGORIZED = "Invalid login or password.";
    
    protected $_user;
    protected $_password;
    protected $_email;
    protected $_rememberMe;

    public function __construct($identity)
    {
        $this->_password = Ivc_Utils::encryptPassword($identity['password']);
        $this->_email = $identity['email'];
        $this->_rememberMe = $identity['remember_me'];
        unset($identity);

        $gateway = new Ivc_Model_Users_Gateway();
        $this->_user = $gateway->createUser(array('email' => $this->_email, 'password' => $this->_password));
    }

    public function authenticate()
    {
        $result = $this->_user->authenticate();
        if ($result->isValid()) {
            $this->initUserSession();
            return true;
        }
        $this->getMessages()->push(Ivc_Message::ERROR, self::FAILURE_UNCATEGORIZED);
        return false;
    }
     
    protected function initUserSession()
    {
        $data = new stdClass();
        $data->userId = $this->_user->user_id;
        $data->roleId = $this->_user->getRoleId();
        
        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($data);
        
        // @todo : set only user's settings to this session (others are located in l10n namespace)
        $sess = Ivc_Model_Users_Session::getInstance();
        $settings = $this->_user->getSettings()->toArray();
        foreach ($settings as $setting => $value) {
            $sess->{$setting} = $value;
        }

        if ((bool) $this->_rememberMe === true)
            Zend_Session::rememberMe(60 * 60 * 24 * 30);
    }
    
    public function getMessages()
    {
        if (null === $this->_message) {
            $this->_message = Ivc_Message::getInstance(Ivc_Message::USERS);            
        }
        return $this->_message;
    }

    public static function isLogged()
    {
        return Zend_Auth::getInstance()->hasIdentity();
    }
}