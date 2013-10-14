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
class Model_Public_Home extends Ivc_Core
{
    CONST EMAIL_SAVED = "Thanks, your email has been saved!";
    CONST CONTACT_MSG_SENT = "Thank you, your message has been sent.";
    
    public function __construct(array $data = null)
    {
        $this->setAclRules();
    }
    
    public function keepMeUpdated($data)
    {
        $dbTable = Zend_Db_Table::getDefaultAdapter();
        $dbTable->insert('newsletter', $data);
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::EMAIL_SAVED);
    }

    public function contact($data)
    {
        $mail = new Ivc_Mail();
        $mail->setRecipient('contact@investiclub.net');
        $mail->setDefaultFrom($data['email'], $data['firstName'] . ' ' . $data['lastName']);
        $mail->setTemplate('contact');
        $mail->title = $data['title'];
        $mail->firstName = $data['firstName'];
        $mail->lastName = $data['lastName'];
        $mail->email = $data['email'];
        $mail->message = $data['message'];
        $mail->send();
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::CONTACT_MSG_SENT);
    }
    
    public function setAclRules()
    {
    	$acl = Zend_Registry::get('Ivc_Acl');
        if ($acl->has($this->getResourceId()))
        	return;
        	
        $acl->add(new Zend_Acl_Resource($this->getResourceId()));
       	$acl->allow(Ivc_Acl::GUEST, $this, array('index'));
       	$acl->deny(Ivc_Acl::USER, $this, array('index'));
       	
        return $this;
    }
    
    public function getResourceId()
    {
        return 'public:home';
    }
}