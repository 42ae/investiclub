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
class Model_Clubs_Clubs extends Ivc_Core
{
    /**
     * Messages Template
     */
    CONST NEW_ROLE_SAVED = "New role saved!";
    CONST NEW_ADMIN_SAVED = "New admin saved!";
    CONST MEMBER_SAVED = "Modification saved!";
    CONST CLUB_CREATED = "Club created!";
    CONST SETTINGS_SAVED = "Settings saved!";
    CONST CLUB_INFO_SAVED = "Club information saved!";
    CONST OTHER = 'OTHER...';
    CONST ERROR_OCCURED = 'An error occured';
    CONST WAITING_FOR_APPROVAL = 'You are currently waiting for approval in club %1$s.';
    CONST MEMBER_ALREADY_ACCEPTED = 'This member has already been added to your club.';
    CONST MEMBER_ACCEPTED = 'This member has been added to your club.';

    public function __construct(array $data = null)
    {
        $this->init($data);
    }
    
    public function editSettings($data)
    {
        if (! $this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception();
        }

        $this->getClub()->getSettings()->populate($data)->save();
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::SETTINGS_SAVED);
    }
    
    public function edit($data)
    {
        if (! $this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception();
        }

        $this->getClub()->populate($data)->save();
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::CLUB_INFO_SAVED);
    }
    
    public function create($data)
    {
        if (! $this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception();
        }
        
        $club = array();
        $member = array();
        $member['role'] = $data['role'];

        if (null == $data['broker_id']) {
            $broker = new Model_Clubs_Brokers(array('name' => $data['broker']));
            $mapper = new Model_Clubs_BrokersMapper();
            $mapper->save($broker);
            $brokerId = $broker->getId();
        } else {
            $brokerId = $data['broker_id'];
        }
        
        // TODO: extrapoler la creation du broker pour l'inserer dans l edition d'un profil club

        $club['name'] = $data['name'];
        $club['country'] = $data['country'];
        $club['currency'] = $data['currency'];
        $club['registration_date'] = $data['registration_date'];
        $club['broker_id'] = $brokerId;
        $club['active'] = true;
        $club['created_on'] = Zend_Date::now()->toString(Zend_Date::ISO_8601);
        
        $club = $this->getClubGateway()->createClub($club);
        $club->save();
        
        $member['user_id'] = Ivc::getCurrentUserId();
        $member['club_id'] = $club->club_id;
        $member['enrollement_date'] = Zend_Date::now()->toString(Zend_Date::ISO_8601);
        $member['admin'] = true;
        $member['pending'] = false;
        $member['status'] = 'active';
        $member['created_on'] = Zend_Date::now()->toString(Zend_Date::ISO_8601);
        
        $club->addMember($member, Ivc::getCurrentUser())->save();

        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::CLUB_CREATED);
    }
    
    public function search($data)
    {
//        if (! $this->checkAcl(__FUNCTION__)) {
//            throw new Ivc_Acl_Exception();
//        }
        
        $clubList = $this->getClubGateway()->fetchClubBySearchString($data['search']);
        return $clubList;

    }
    
    public function acceptMemberRequest($encryptedMemberId)
    {
        if (! $this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception();
        }
                
        $memberId = Ivc_Utils::decryptText($encryptedMemberId);
        $member = $this->getClubGateway()->fetchMemberById($memberId);
        if (null == $member) {
            $this->getMessageInstance()->push(Ivc_Message::ERROR, self::ERROR_OCCURED);
            return;
        }
        
        if (false == $member->pending AND 'active' == $member->status) {
            $this->getMessageInstance()->push(Ivc_Message::WARNING, self::MEMBER_ALREADY_ACCEPTED);
            return;
        }
        
        $member->pending = false;
        $member->status = 'active';
        $member->save();
        
        // remove notification from admin dashboard
        $notifications = Ivc::getCurrentUser()->getNotificationsFrom($member->getUser()->user_id);
        foreach ($notifications as $notification) {
            $json = json_decode($notification->json);
            if ($notification->notification_type === 'club' AND $json->message == 'NOTIFICATION_MEMBER_JOIN_REQUEST_FOR_ADMIN') {
                $notification->deleted = true;
                $notification->save();
            }
        }
        
        // remove notification from new member dashboard
        $notifications = $member->getUser()->getNotifications();
        foreach ($notifications as $notification) {
            $json = json_decode($notification->json);
            if ($notification->notification_type === 'club' 
                AND $json->message == 'NOTIFICATION_MEMBER_JOIN_REQUEST_FOR_USER'
                AND $json->params->clubName == $member->getClub()->name) {
                $notification->deleted = true;
                $notification->save();
            }
        }
        
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::MEMBER_ACCEPTED);
        
    }
    
    /**
     * Check whether a user is waiting for approval or not
     * 
     * @param Ivc_Model_Users_User $user
     * @return boolean true|false
     */
    public function isWaitingForApproval($user)
    {
//        if (! $this->checkAcl(__FUNCTION__)) {
//            throw new Ivc_Acl_Exception();
//        }

        $member = $this->getClubGateway()->fetchWaitingForApprovalMember($user);
        if (null !== $member) {
            $this->getMessageInstance()->push(Ivc_Message::SUCCESS, sprintf(self::WAITING_FOR_APPROVAL, $member->getClub()->name));
            return true;
        }
        return false;
        
    }

    public function sendRequestToJoinClub()
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception();
        }
        
        $club = $this->getClub();
        
        if (null === $club) {
            throw new Ivc_Exception('Page error');
        }
        
        $member['user_id'] = Ivc::getCurrentUserId();
        $member['club_id'] = $club->club_id;
        $member['enrollement_date'] = Zend_Date::now()->toString(Zend_Date::ISO_8601);
        $member['admin'] = false;
        $member['role'] = 'member';
        $member['pending'] = true;
        $member['status'] = 'inactive';
        $member['created_on'] = Zend_Date::now()->toString(Zend_Date::ISO_8601);
        
        $member = $club->addMember($member, Ivc::getCurrentUser());
        $member->save();
        
        $mail = new Ivc_Mail;
        // The one who's going to receive the new member request
        $clubAdmin = $this->getClubGateway()->fetchAdminByClubId($club->club_id)->getUser();
        $mail->setTemplate(Ivc_Mail::REQUEST_JOIN_CLUB);
        $mail->encryptedMemberId = Ivc_Utils::encryptText($member->member_id);
        // TODO: $mail->declineUrl = ...;
        $mail->clubName = $club->name;
        $mail->firstName = $member->getUser()->first_name;
        $mail->lastName = $member->getUser()->last_name;

        if (APPLICATION_ENV === 'development') {
            // If development server, send to dev@investiclub.net in all cases
            $mail->setRecipient(Zend_Registry::get('config')->email->defaultRecipient);
        } else {
            $mail->setRecipient($clubAdmin->email);
        }

        $mail->send();
        
        $userGateway = new Ivc_Model_Users_Gateway();
        // admin notification
        $json = array('message' => 'NOTIFICATION_MEMBER_JOIN_REQUEST_FOR_ADMIN', 
        			  'params' => array('firstName' => Ivc::getCurrentUser()->first_name,
                                        'lastName' => Ivc::getCurrentUser()->last_name,
                                        'memberId' => $member->member_id,
        ));
        $adminNotification = array(
            'sender_id'         => Ivc::getCurrentUserId(),
            'recipient_id'      => $clubAdmin->user_id,
            'notification_type' => 'club',
            'json'      => json_encode($json),
            'is_read'              => false,
            'deleted'              => false,
            'last_update'       => null);
        
        // user notification
        $json = array('message' => 'NOTIFICATION_MEMBER_JOIN_REQUEST_FOR_USER', 
        			  'params' => array('clubName' => $club->name,
        ));
        $userNotification = array(
            'sender_id'         => null,
            'recipient_id'      => Ivc::getCurrentUserId(),
            'notification_type' => 'club',
            'json'      => json_encode($json),
            'is_read'              => false,
            'deleted'              => false,
            'last_update'       => null);
        
        $userGateway->createNotification($adminNotification)->save();
        $userGateway->createNotification($userNotification)->save();
        
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::CLUB_CREATED);
    }

    public function join($data)
    {
        if (! $this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception();
        }

        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::MEMBER_DELETED);
    }

    /**
     * @return Ivc_Model_Clubs_Club
     */
    public function view()
    {
        if (! $this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception();
        }
        return $this->getClub();
    }
    
    public function setAclRules()
    {
		$acl = Zend_Registry::get('Ivc_Acl');
		if (!$acl->has($this->getResourceId())) {
    		$acl->add(new Zend_Acl_Resource($this->getResourceId()));
    		
    		// Guests and users ACL
    		$acl->allow(Ivc_Acl::USER, $this, array('index', 'join', 'create'));	    
            $acl->allow(Ivc_Acl::USER, $this, array('sendRequestToJoinClub'));
            
            // Club ACL
    		if ($this->getClubId()) {
                $acl->deny(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, array('join', 'create', 'sendRequestToJoinClub'))
                    ->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, array('list', 'performances', 'reports', 'view'))
                    ->allow(Ivc_Acl::CLUB_ADMIN  . $this->getClubId(), $this, array('edit', 'editSettings', 'acceptMemberRequest'));
    		}
		}
		
		// Load dynamic privileges from database
		Ivc_Acl_Factory::setDynAcl($acl, $this);
		
        return $this;
    }
    
    public function getResourceId()
    {
        return 'club:' . $this->getClubId() . ':clubs';
    }
}