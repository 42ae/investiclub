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
 * @package		Ivc_Controller
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */
/**
 * Plugin ...
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Controller
 * @subpackage	Plugin
 */
class Ivc_Controller_Plugin_Session extends Zend_Controller_Plugin_Abstract
{
    protected $_session;

    /**
     * Constructor
     * 
     */
    public function __construct()
    {
        $this->_session = Zend_Registry::get('session.request');
    }

    public function dispatchLoopShutdown()
    {
        $this->_session->requestUri = $this->getRequest()->getRequestUri();
    }
} 