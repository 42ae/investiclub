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
 * Join club model
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Clubs
 */
class Model_Clubs_Join
{
    
    CONST REQUEST_ACCEPT = "You are now in a club";
    CONST REQUEST_REFUSE = "You refused the request from CLub Name";
    
    protected $_acl;
    protected $_currentUser;
    
    protected $_message = null;

    /**
     * Join constructor
     */
    public function __construct()
    {
        //$this->setAcl(Zend_Registry::get('Ivc_Acl'));
        $this->setCurrentUser(Ivc::getCurrentUser());
    }

    /**
     */
    public function joinRequest($accept)
    {
        $member = $this->getCurrentUser()->getMember('pending');
        if (null !== $member AND true === $accept) {
            $member->status = 'active';
            $member->save();
            $this->getMessages()->push(Ivc_Message::SUCCESS, self::REQUEST_ACCEPT);
        } elseif (null !== $member AND false === $accept) {
            $model = new Model_Users_Signup();
            $data = array('first_name' => $this->getCurrentUser()->first_name,
            			  'last_name'  => $this->getCurrentUser()->last_name,
                          'password'   => Ivc_Utils::generateRandomPassword(),
                          'email'      => null);
            $user = $model->registerUser($data);
            $member->user_id = $user->user_id;
            $member->status = 'active';
            $member->save();
            $this->getMessages()->push(Ivc_Message::WARNING, self::REQUEST_REFUSE);
        }
    }
    
    public function setCurrentUser(Ivc_Model_Users_User $currentUser)
    {
        $this->_currentUser = $currentUser;
    }
    
   public function getCurrentUser()
    {
        if (null === $this->_currentUser) {
            $this->setCurrentUser(Ivc::getCurrentUser());
        }
        return $this->_currentUser;
    }
    
    public function getMessages()
    {
        if (null === $this->_message) {
            $this->_message = Ivc_Message::getInstance(Ivc_Message::MEMBERS);            
        }
        return $this->_message;
    }
}