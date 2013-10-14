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
class Ajax_IndexController extends Zend_Controller_Action
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
        $ajaxContext->addActionContext('tz', 'json')
                    ->addActionContext('keep-me-updated', 'json')
                    ->addActionContext('contact', 'json')
                    ->initContext();
    }

    public function indexAction()
    {
        //default action
    }
    
    public function tzAction()
    {
        Zend_Registry::get('session.l10n')->timezone = $this->_getParam('name', 'UTC');
    }
    
    public function keepMeUpdatedAction()
    {
        $model = new Model_Public_Home();
        $form = new Form_Public_KeepMeUpdated();
        
        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $model->keepMeUpdated($data);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
        
    }

    public function contactAction()
    {
        $model = new Model_Public_Home();
        $form = new Form_Public_Contact();
        
        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $model->contact($data);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
        
    }
}