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
 * Index ajax controller
 * 
 * Handles Ajax request
 * 
 * @author      Alexandre Esser
 * @category    InvestiClub
 * @package     Controller
 */
class Ajax_DashboardController extends Zend_Controller_Action
{
    /**
     * User's instance
     * @var Ivc_Model_Users_User $_user
     */
    protected $_user = null;
    
    public function preDispatch()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->redirector('index', 'index', 'default');
        }
    }
    
    public function init()
    {
        $this->_user = Ivc::getCurrentUser();
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('mark-as-read', 'json')
                    ->initContext();
    }

    public function indexAction()
    {
        //default action
    }
    
    public function markAsReadAction()
    {
        $model = new Model_Dashboard_Dashboard();
        if ($this->_getParam('id')) {
            $model->markAsRead($this->_getParam('id'));
            $notice = $model->getMessages();
            $this->view->messages = $notice;
        }
    }

}