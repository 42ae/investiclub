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
 * @subpackage	Users
 */
class Model_Users_Users extends Ivc_Core
{
    protected $_acl;
    protected $_message = null;
    
    /**
     * Messages Template
     * @var array
     */
    CONST USER_EDIT_SAVED = "User profile saved!"; 
    CONST USER_SETTINGS_SAVED = "User settings saved!"; 
    CONST FORM_ERROR = "Le message n'a pas correctement été envoyé. Assurez-vous d'avoir renseigné un destinataire et un contenu."; 
    CONST MESSAGE_SENT = "Message envoyé.";

    public function __construct(array $data = null)
    {
        //$this->_userId = Ivc::getCurrentUserId();
        //$this->_user = Ivc::getCurrentUser();
        
        if (isset($data['userId'])) {
            //$this->_userId = $data['userId'];
            $this->setUserId($data['userId']);
            //$this->_user = Ivc::getCurrentUser()->getGateway()->fetchUser($this->_userId);
            //if (null == $this->_user) {
            //    throw new Ivc_Exception(Ivc_Message::ERROR, 'Invalid User Id');                
            //}
            if ($this->getUser() == null)
            	throw new Ivc_Exception(Ivc_Message::ERROR, 'Invalid User Id');
        }
        
        $this->setAclRules();
    }
    
    public function viewUser()
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception;
        }
        
        return $this->getUser();
    }
    
    public function editUser($data)
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception;
        }

        unset($data['current_password']);
        unset($data['confirm_password']);
        unset($data['password']);
        unset($data['token']);
        
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::USER_EDIT_SAVED);
        $this->getUser()->populate($data)->save();
    }

    public function editSettings($data)
    {
        if (!$this->checkAcl(__FUNCTION__)) {
            throw new Ivc_Acl_Exception;
        }
        
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::USER_SETTINGS_SAVED);
        $this->getUser()->getSettings()->populate($data)->save();
    }

    public function checkAcl($action)
    {
        return $this->getAcl()->isAllowed(Ivc::getCurrentUser(), $this, $action);
    }

    public function setAclRules()
    {
    	$acl = Zend_Registry::get('Ivc_Acl');
        if ($acl->has($this->getResourceId()))
        	return;
        	
        $acl->add(new Zend_Acl_Resource($this->getResourceId()));
        if ($this->getUserId()) {
          	$acl->allow(Ivc_Acl::IVC_ADMIN, $this, array('editUser', 'editSettings'));
          	$acl->allow(Ivc_Acl::CLUB_MEMBER, $this, array('sendMessage'));
          	$acl->allow(Ivc_Acl::USER, $this, array('viewUser'));
          	$acl->allow($this->getUserId(), $this, array('editUser', 'editSettings'));
        }
        // Set dynamic rules, works for external users rights
        Ivc_Acl_Factory::setDynAcl($acl, $this);
        return $this;
    }
    
    public function date_compare($a, $b)
    {
        $t1 = strtotime($b['timestamp']);
        $t2 = strtotime($a['timestamp']);
        return $t1 - $t2;
    }    
    

    public function getResourceId()
    {
        return 'user:' . $this->getUserId() . ':user';
    }

    public function getAcl()
    {
        if (!$this->_acl)
            $this->_acl = Zend_Registry::get('Ivc_Acl');
        return $this->_acl;
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }
}