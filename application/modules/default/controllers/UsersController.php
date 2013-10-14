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
 * Users controller
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Controller
 */
class UsersController extends Zend_Controller_Action
{
    
    public function preDispatch()
    {
        $this->_helper->navigation()->renderBreadcrumbs();
        $this->_helper->navigation()->renderSubMenu();
    }

    public function init()
    {}


    public function indexAction()
    {
        $this->_helper->redirector('view', 'users');
    }
    
    public function viewAction()
    {

        $id = $this->_getParam('id', Ivc::getCurrentUserId());
        $model = new Model_Users_Users(array('userId' => $id));
        $this->view->user = $model->viewUser();
        
        
        $this->view->hideBreadcrumbs = true;
        if ($id AND $id != Ivc::getCurrentUserId()) {
            $this->view->hideSidebar = true;
        }
    }

    /**
     * Edit action
     * 
     * Renders a {@link Form_Users_Edit} form and populates it according
     * to the currenct user's preferences.
     */
    public function editAction()
    {
        $model = new Model_Users_Users();
        $form = new Form_Users_Edit();
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $data = $form->getValues();
                $model->editUser($data);
                $formFields = $model->getUser()->toArray();
                $messages = $model->getMessages();
            } else {
                $formFields = $form->getValues();
                $messages = $form->getMessages();
            }
            $this->_helper->flashMessenger->addMessage($messages);
            $this->_helper->redirector('edit', 'users', 'default');
        } else {
            $formFields = $model->getUser()->toArray();            
        }

        $form->populate($formFields);
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->view->form = $form;
    }

    public function settingsAction()
    {
        $model = new Model_Users_Users();
        $form = new Form_Users_Settings();
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $data = $form->getValues();
                $model->editSettings($data);
                $formFields = $model->getUser()->getSettings()->toArray();
                $messages = $model->getMessages();
            } else {
                $formFields = $form->getValues();
                $messages = $form->getMessages();
            }
            $this->_helper->flashMessenger->addMessage($messages);
            $this->_helper->redirector('settings', 'users', 'default');
        } else {
            $formFields = $model->getUser()->getSettings()->toArray();            
        }

        $form->populate($formFields);
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->view->form = $form;
    }
    
    public function privacyAction()
    {
        
    }

    public function messagesAction()
    {
        $members = new Model_Members_Members();
        $form = new Form_Users_CreateMessage();
        
        $listMembers = $members->listMembers();
        
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $members->sendMessage($request->getPost());
            $messages = $members->getMessages();
            $this->view->messages = $messages;
        }
        
        $messagesList = $members->getMessagesList();
        
        $this->view->form = $form;
        $this->view->members = $listMembers;
        $this->view->messagesList = $messagesList;
    }
}

