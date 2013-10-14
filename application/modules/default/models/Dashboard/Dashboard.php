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
class Model_Dashboard_Dashboard extends Ivc_Core
{
    protected $_message = null;
    
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

    public function __construct(array $data = null)
    {
    	if (isset($data['clubId'])) {
    		$this->setClubId($data['clubId']);
    	}

        if ($this->getClubId() && null == $this->getClub()) {
        	throw new Ivc_Exception(Ivc_Message::ERROR, 'Invalid Club Id');
        }
        
        $this->setAclRules();
    }
    
    public function markAsRead($id)
    {
        $id = Ivc_Utils::decryptText($id);
        $notification = Ivc::getCurrentUser()->getNotificationById($id);
        if ($notification AND false == $notification->is_read) {
            $notification->is_read = true;
            $notification->save();
            $this->getMessageInstance()->push(Ivc_Message::SUCCESS, true);
        }
    }

    public function index()
    {
        if (! $this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception();
        }
    }
    
    public function setAclRules()
    {
    	$acl = Zend_Registry::get('Ivc_Acl');
        if ($acl->has($this->getResourceId()))
        	return;
        	
        $acl->add(new Zend_Acl_Resource($this->getResourceId()));
        
       	$acl->deny(Ivc_Acl::GUEST, $this, array('index'));
       	$acl->allow(Ivc_Acl::USER, $this, array('index'));
       	
        // Set dynamic rules, works for external users rights
        Ivc_Acl_Factory::setDynAcl($acl, $this);
        return $this;
    }
    
    public function getResourceId()
    {
        return 'club:' . $this->getClubId() . ':dashboard';
    }
}