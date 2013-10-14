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
 * @subpackage	Members
 */
class Model_Members_Members extends Ivc_Core
{
    /**
     * Messages Template
     * @var array
     */
    CONST NEW_ROLE_SAVED = "New role saved!"; 
    CONST NEW_ADMIN_SAVED = "New admin saved!"; 
    CONST MEMBER_SAVED = "Modification saved!"; 
    CONST MEMBER_DELETED = "Member deleted!"; 
    CONST FORM_ERROR = "Le message n'a pas correctement été envoyé. Assurez-vous d'avoir renseigné un destinataire et un contenu."; 
    CONST MESSAGE_SENT = "Message envoyé."; 
    CONST OTHER = 'OTHER...';

    public function __construct(array $data = null)
    {
        $this->init($data);
    }
    
    public function makeAdmin($id)
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception;
        }
        
        if (null == Ivc::getCurrentMember()) {
            return $this->getMessageInstance()->push(Ivc_Message::ERROR, "You're not admin of this club.");
        }        
        
        $member = $this->getMemberById($id);
        Ivc::getCurrentMember()->makeAdmin($member);
        
        $this->getCache()->cleanClub();
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::NEW_ADMIN_SAVED);
    }
    
    public function deleteMember($id)
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception;
        }
        
        $member = $this->getMemberById($id);
        $member->delete();
        // @todo: delete from treasury - send notification - send report to the user
        
        $this->getCache()->cleanClub();
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::MEMBER_DELETED);
    }
    
    public function editMember($id, $data)
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception;
        }
        
        $member = $this->getMemberById($id);
        
        if ($member->isActive() OR $member->isUnregistered()) {
            $member->edit($data);
            $this->getCache()->cleanClub();
            $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::MEMBER_SAVED);
        }
        else if ($member->isPending()) {
            $this->_editPendingMember($member, $data);            
        }
    }
    
    public function addEmail($data)
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception;
        }
        
        $member = $this->getMemberById($data['id']);
        if (false === $member->isUnregistered()) {
            throw new Ivc_Exception(Ivc_Exception::ERROR_OCCURRED, Zend_Log::ERR);
        }
        
        if (null !== ($user = $this->getUserByEmail($data['email']))) { // if it's a registered user
            if (null != ($m = $this->getClubGateway()->fetchMember($user))) { // who is member
                if ($m->isActive() OR $m->isPending()) { // and active or pending
                    return $this->getMessageInstance()->push(Ivc_Message::ERROR, "This user has already a club.");
                }
            }
            $member->pending = true;
            $member->save();
            $this->_sendJoinClubRequest($user);
            
            $this->getCache()->cleanClub();
            $this->getMessageInstance()
                 ->push(Ivc_Message::SUCCESS, "Join club request sent to " . $user->first_name . " " . $user->last_name);
            // @todo: add notification to newUser, remove notification to user
        } else {
            $password = Ivc_Utils::generateRandomPassword();
            $user = $member->getUser();
            $user->password = Ivc_Utils::encryptPassword($password);
            $user->email = $data['email'];
            $user->save();
            
            $member->pending = true;
            $member->save();
            
            $this->_sendJoinClubRequestToNewUser($user, $password);
            // @todo: add notification to newUser
            
            $this->getCache()->cleanClub();
            $this->getMessageInstance()
                 ->push(Ivc_Message::SUCCESS, "Join club request sent to " . $user->first_name . " " 
                                                                           . $user->last_name);
        }
    }    
    
    public function addMember($data)
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception;
        }
        
        if ($data['email']) {
            if (null !== ($user = $this->getUserByEmail($data['email']))) { // if it's a registered user
                if (null != ($member = $this->getClubGateway()->fetchMember($user))) { // who is member
                    if ($member->isActive() OR $member->isPending()) { // and not active or pending
                        return $this->getMessageInstance()->push(Ivc_Message::ERROR, "This user has already a club.");
                    }
                } // so it's a user without a club, let's register him
                $member = $this->_registerMember($user, $data);
                $this->_sendJoinClubRequest($user);
                // @todo: add notification to existingUser
                
                $this->getCache()->cleanClub();
                $this->getMessageInstance()
                     ->push(Ivc_Message::SUCCESS, "Join club request sent to " . $user->first_name . " " . $user->last_name);
            } else { // we've got an email but not registered yet, let's proceed
                $password = Ivc_Utils::generateRandomPassword();
                $user = $this->_registerUser(array_merge($data, array('password' => $password)));
                $member = $this->_registerMember($user, $data);
                $this->_sendJoinClubRequestToNewUser($user, $password);
                // @todo: add notification to newUser 
                
                $this->getCache()->cleanClub();
                $this->getMessageInstance()
                     ->push(Ivc_Message::SUCCESS, "Join club request sent to " . $user->first_name . " " . $user->last_name);
            }    
        } else { // e-mail not specified, create a virtual user
            $password = Ivc_Utils::generateRandomPassword();
            $user = $this->_registerUser(array_merge($data, array('password' => $password)));
            $member = $this->_registerMember($user, $data);
            $member->status = 'active';
            $member->pending = false;
            $member->save();
            
            $this->getCache()->cleanClub();
            $this->getMessageInstance()
                 ->push(Ivc_Message::SUCCESS, "Join club request sent to " . $user->first_name . " " . $user->last_name);
        }
        
        
    }
    
    public function listMembers()
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception;
        }
        
        $members = $this->getClub()->getMembers();
        return $members;
    }

    public function listPendingMembers()
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception;
        }
        
        $members = $this->getClub()->getMembers(array('status'     => array('inactive'), 
        											  'pending'    => array(true)));
        return $members;
    }
    
    private function _editPendingMember($member, $data)
    {
        
        if ($member->getUser()->email != $data['email'])
        {
            if (null !== ($user = $this->getUserByEmail($data['email']))) { // if it's a registered user
                if (null != ($member = $this->getClubGateway()->fetchMember($user))) { // who is member
                    if ($member->isActive() OR $member->isPending()) { // and active or pending
                        return $this->getMessageInstance()->push(Ivc_Message::ERROR, "This user has already a club.");
                    }
                }
                $member->user_id = $user->user_id;
                $member->save();
                $this->_sendJoinClubRequest($user);
                // @todo: add notification to newUser, remove notification to user
            } else {
                $password = Ivc_Utils::generateRandomPassword();
                $data['password'] = $password;
                $data['first_name'] = $member->getUser()->first_name;
                $data['last_name'] = $member->getUser()->last_name;
                $user = $this->_registerUser($data);
                
                $member->user_id = $user->user_id;
                $member->save();
                
                $this->_sendJoinClubRequestToNewUser($user, $password);
            }
        }
        $member->edit($data);

        $this->getCache()->cleanClub();
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::MEMBER_SAVED);
    }
    
    private function _registerUser($userData)
    {
        $data['first_name'] = $userData['first_name'];
        $data['last_name']  = $userData['last_name'];
        $data['email']      = $userData['email'] ?: null;
        $data['password']   = Ivc_Utils::encryptPassword($userData['password']);
        $data['created_on'] = Zend_Date::now()->toString(Zend_Date::ISO_8601);
        
        $gateway = new Ivc_Model_Users_Gateway();
        $user = $gateway->createUser($data);
        $user->save();
        return $user;
    }
    
    private function _registerMember($user, $form)
    {
        $data = array('member_id'	     => null,
        			  'club_id'          => $this->getClub()->club_id,
                      'user_id'          => $user->user_id,
                      'role'             => $form['role'],
                      'enrollement_date' => Zend_Date::now()->toString(Zend_Date::ISO_8601),
                      'admin'            => false,
                      'status'           => 'inactive',
                      'pending'          => true,
                      'created_on'       => Zend_Date::now()->toString(Zend_Date::ISO_8601));
        
        $member = $user->addToClub($this->getClub(), $data);
        $member->save();
        return $member;
    }
    
    
//    private function _registerNotification($user)
//    {
//        $data = array(
//        'notification_id'   => null, 
//        'sender_id'         => null, 
//        'recipient_id'      => $user->user_id,
//        'notification_type' => 'warning',
//        'notification'      => 'You have an invitation from ...');
//
//        
//        $userGateway = new Ivc_Model_Users_Gateway();
//        $notification = $userGateway->createNotification($data);
//        $notification->save();
//    }
    
    private function _sendJoinClubRequest($user)
    {
        $mail = new Ivc_Mail;
        $mail->setRecipient("alexandre@esser.fr");
        $mail->setTemplate(Ivc_Mail::JOIN_CLUB_REQUEST);
        $mail->clubName = $this->getClub()->name;
        $mail->senderFirstName = Ivc::getCurrentUser()->first_name;
        $mail->senderLastName = Ivc::getCurrentUser()->last_name;
        $mail->email    = $user->email;
        $mail->firstName = $user->firstName;
        $mail->lastName = $user->lastName;
        $mail->send();
    }

    private function _sendJoinClubRequestToNewUser($user, $password)
    {
        $mail = new Ivc_Mail;
        $mail->setRecipient("alexandre@esser.fr");
        $mail->setTemplate(Ivc_Mail::JOIN_CLUB_REQUEST_NEW_USER);
        $mail->clubName = $this->getClub()->name;
        $mail->senderFirstName = Ivc::getCurrentUser()->first_name;
        $mail->senderLastName = Ivc::getCurrentUser()->last_name;
        $mail->email = $user->email;
        $mail->password = $password;
        $mail->firstName = $user->firstName;
        $mail->lastName = $user->lastName;
        $mail->send();
    }
    
    public function setAclRules()
    {
    	$acl = Zend_Registry::get('Ivc_Acl');
        if ($acl->has($this->getResourceId()))
        	return;
        	
        $acl->add(new Zend_Acl_Resource($this->getResourceId()));
        if ($this->getclubId()) {
          	$acl->allow(Ivc_Acl::CLUB_MEMBER . $this->getclubId(), $this, array('listMembers', 'listPendingMembers', 'link', 'close', 'sendMessage'))
                ->allow(Ivc_Acl::CLUB_ADMIN  . $this->getclubId(), $this, array('makeAdmin', 'addEmail', 'addMember', 'editMember', 'deleteMember'))
                ->allow(Ivc_Acl::CLUB_ADMIN  . $this->getclubId(), $this, array('addMember'));
        }
        // Set dynamic rules, works for external users rights
        Ivc_Acl_Factory::setDynAcl($acl, $this);
        return $this;
    }

    public function sendMessage($data)
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception;
        }
        
        if ($data['recipients'] == '[]') {
            $data['recipients'] = '';
        }
        
        if (!(isset($data['recipients']) AND $data['recipients']) OR !(isset($data['message']) AND $data['message'])) {
            $this->getMessageInstance()->push(Ivc_Message::ERROR, self::FORM_ERROR);
            return;
        }
        
        // Check recipients list
        $recipients = json_decode($data['recipients']);
        $members = $this->listMembers();
        $membersFullName = array();
        foreach ($members as $member) {
            $membersFullName[] = $member->getUser()->getFullName();
        }
        
        foreach ($recipients as $recipient) {
            if (false == in_array($recipient, $membersFullName)) {
                $this->getMessageInstance()->push(Ivc_Message::ERROR, self::FORM_ERROR);
                return;
            }
        }
        
        // Remove sender when send to all team members
        if (isset($data['sendtoall']) AND $data['sendtoall'] == true) {
            foreach ($recipients as $key => $recipient) {
                if ($recipient == $this->getUser()->getFullName()) {
                    unset($recipients[$key]);
                }
            }
        }
        
        // Array unique to send message only once to all recipients
        $recipients = array_unique($recipients);
        
        foreach ($recipients as $recipient) {
            foreach ($members as $member) {
                if ($member->getUser()->getFullName() == $recipient) {
                    $this->getUser()->sendMessage($member->getUser()->user_id, $data['subject'], $data['message'], 'general');
                }
            }
        }
        
        $this->getCache()->cleanClub();
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::MESSAGE_SENT);
    }
    
    public function getMessagesList()
    {
        $messages = $this->getUserGateway()->fetchAllSentAndReceivedMessagesByUserId(Ivc::getCurrentUserId(), 'general');
        
                
        foreach ($messages as $k => $v) {
            $messages[$k]['recipient_id'] = $this->getUserGateway()->fetchUser($v['recipient_id'])->getFullName();
            $messages[$k]['sender_id'] = $this->getUserGateway()->fetchUser($v['sender_id'])->getFullName();
        }
        
        $original = $messages;
        
        $res = array();
        foreach ($messages as $k => $v) {
            foreach ($messages as $kk => $vv) {
                if ($v['sender_id'] == $vv['sender_id'] AND $v['timestamp'] == $vv['timestamp'] AND $v['subject'] == $vv['subject'] AND $v['message'] == $vv['message']) {
                    $res[$k] = $v;
                    unset($messages[$k]);
                    unset($messages[$kk]);
                }
            }
        }
        
        $array = $res;
        foreach ($res as $k => $result) {
            foreach ($original as $message) {
                if ($result['sender_id'] == $message['sender_id'] AND $result['timestamp'] == $message['timestamp'] AND $result['subject'] == $message['subject'] AND $result['message'] == $message['message']) {
                    if ($array[$k]['recipient_id'] != $message['recipient_id'])
                        $array[$k]['recipient_id'] .= ', ' . $message['recipient_id'];
                }
            }
        }
        
        usort($array, function($a, $b) {
              return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        return $array;
    }
    
    
    public function getResourceId()
    {
        return 'member:' . $this->getclubId() . ':members';
    }
    
    public function getAllowedAction()
    {
        $actions = array('addMember', 'deleteMember', 'addEmail', 'editMember', 'listMembers', 'listPendingMembers', 'makeAdmin');
        
        $allowedAction = array();
        foreach ($actions as $action) {
            if ($this->checkAcl($action)) {
                $allowedAction[] = $action;
            }
        }
        return $allowedAction;
    }

    public function getMemberById($id)
    {
        $member = $this->getClubGateway()->fetchMemberById($id);
        if (null == $member) {
            throw new Ivc_Exception(Ivc_Exception::ERROR_OCCURRED, Zend_Log::ERR);
        }
        return $member;
    }

    public function getUserByEmail($email)
    {
        if (!$email) {
            return;
        }
        
        $gateway = new Ivc_Model_Users_Gateway();
        return $gateway->fetchByEmail($email);
    }
}