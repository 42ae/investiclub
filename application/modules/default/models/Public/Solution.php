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
class Model_Public_Solution extends Ivc_Core
{
    public function __construct(array $data = null)
    {
        $this->setAclRules();
    }

    public function setAclRules()
    {
    	$acl = Zend_Registry::get('Ivc_Acl');
        if ($acl->has($this->getResourceId()))
        	return;
        	
        $acl->add(new Zend_Acl_Resource($this->getResourceId()));
       	$acl->allow(Ivc_Acl::GUEST, $this, array('solution'));
       	$acl->deny(Ivc_Acl::USER, $this, array('solution'));
       	
        return $this;
    }
    
    public function getResourceId()
    {
        return 'public:solution';
    }
}