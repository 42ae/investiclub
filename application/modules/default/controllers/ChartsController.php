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
 * @package		Controller
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Members controller
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Controller
 */
class ChartsController extends Zend_Controller_Action
{
    /**
     * User's instance
     * @var Ivc_Model_Users_User $_user
     */
    protected $_gateway;
    protected $_member;
    
    public function preDispatch()
    {
        $this->_helper->navigation()->renderBreadcrumbs();
        $this->_helper->navigation()->renderSubMenu();
    }

    public function init()
    {
        $this->_gateway = new Ivc_Model_Clubs_Gateway();
        $this->_member = $this->_gateway->fetchMember(Ivc::getCurrentUser());
    }

    public function indexAction()
    {
        $this->_helper->redirector('shares', 'members', 'default');
    }

}

