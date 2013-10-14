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
 * Club controller
 * 
 * Manage clubs and handle different actions such as create, add, remove and
 * edit. This controller is also in charge of the relationship between members
 * and clubs.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Controller
 */
class ClubsController extends Zend_Controller_Action
{
    
    public function preDispatch()
    {
        $this->_helper->navigation()->renderBreadcrumbs();
        $this->_helper->navigation()->renderSubMenu();
    }

    public function init()
    {
        /* Initialize action controller here */
    }

    /**
     * Index action
     * 
     * Redirect to the view action
     */
    public function indexAction()
    {
        $model = new Model_Clubs_Clubs();
        $model->checkAcl('index');
                
        $this->_helper->redirector('view', 'clubs');
        // action body
    }
    
    /**
     * View action
     * 
     * Display a club overview
     */
    public function viewAction()
    {
        $id = $this->_getParam('id', null);
        $model = new Model_Clubs_Clubs(array('clubId' => $id));
        $this->view->club = $model->view();
        
        
        $this->view->hideBreadcrumbs = true;
        if ($id AND $id != Ivc::getCurrentUserId()) {
            $this->view->hideSidebar = true;
        }
    }

    public function infoAction()
    {
        // action body
    }

    /**
     * Create action
     * 
     * Create a club and send an e-mail notification
     * @todo Let a new user import his current club data
     */
    public function createAction()
    {
        $request = $this->getRequest();
        $type = $this->_getParam('type');
        if ($type === 'new') {
            $this->_helper->redirector('create-new-club', 'clubs', 'default');
        } elseif ($type === 'import') {
            echo "lol";//$this->_forward('createeClub');
        }
    }

    public function createNewClubAction()
    {
        $flashMessenger = $this->_helper->flashMessenger;
        $form = new Form_Clubs_Create();
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $model = new Model_Clubs_Clubs();
                $data = $form->getValues();
                $model->create($data);
                $messages = $model->getMessages();
                $this->renderScript('/clubs/create-club-confirmation.phtml');
            } else {
                $formFields = $form->getValues();
                $messages = $form->getMessages();
            }
            $flashMessenger->addMessage($messages);
        }

        $this->view->messages = $flashMessenger->getCurrentMessages();
        $this->view->form = $form;

        $flashMessenger->clearCurrentMessages();
    }

    protected function _createExistingClub()
    {
        $this->render('importExistingClub');        
    }
    
    public function joinAction()
    {
        // Club request acceptance (user invited by a club)
        if ($this->_hasParam('accept')) {
            $accept = (bool) filter_var($this->_getParam('accept'), FILTER_VALIDATE_BOOLEAN);
            $model = new Model_Clubs_Join();
            $model->joinRequest($accept);
            
            $message = $model->getMessages()->toArray();
            $this->_helper->flashMessenger($message);
            if ($accept)
                $this->_helper->redirector('list', 'members', 'default');
            $this->_helper->redirector('index', 'dashboard', 'default');
        }
        
        // Link sent to an admin after a member join request
        if ($this->_hasParam('accept-member')) {
            $model = new Model_Clubs_Clubs();
            $model->acceptMemberRequest($this->_getParam('accept-member'));
            $message = $model->getMessages();
            $this->_helper->flashMessenger($message);
            $this->_helper->redirector('index', 'dashboard', 'default');
        }
        
        // Request to join a club from user
        if ($this->_hasParam('member-join-request')) {
            $clubId = $this->_getParam('member-join-request');
            $club = new Model_Clubs_Clubs(array('clubId' => $clubId));
            $club->sendRequestToJoinClub();
            $this->_helper->redirector('dashboard', 'index', 'default');
        }
        
        // prevent user to access form is already waiting or approval 
                
        $form = new Form_Clubs_Join();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $club = new Model_Clubs_Clubs();
                $data = $form->getValues();
                $results = $club->search($data);
                $this->view->results = $results;
            } else {
                $messages = $form->getMessages();
                $this->view->formError = $messages;
            }
        }
        $this->view->form = $form;
        $this->view->headScript()->prependFile('https://www.google.com/jsapi');
    }
    
    public function editAction()
    {
        $flashMessenger = $this->_helper->flashMessenger;
        $request = $this->getRequest();
        $model = new Model_Clubs_Clubs();
        $form = new Form_Clubs_Information();
        
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $data = $form->getValues();
                $model->edit($data);
                $formFields = $model->getClub()->toArray();
                $messages = $model->getMessages();
            } else {
                $formFields = $form->getValues();
                $messages = $form->getMessages();
            }
            $flashMessenger->addMessage($messages);
        } else {
            $formFields = $model->getClub()->toArray();            
        }

        $form->populate($formFields);
        $this->view->form = $form;

        $this->view->messages = $flashMessenger->getCurrentMessages();
        $flashMessenger->clearCurrentMessages();
    }
    
    public function settingsAction()
    {
        $flashMessenger = $this->_helper->flashMessenger;
        $request = $this->getRequest();
        $model = new Model_Clubs_Clubs();
        $form = new Form_Clubs_Settings();
        
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $data = $form->getValues();
                $model->editSettings($data);
                $formFields = $model->getClub()->getSettings()->toArray();
                $messages = $model->getMessages();
            } else {
                $formFields = $form->getValues();
                $messages = $form->getMessages();
            }
            $flashMessenger->addMessage($messages);
        } else {
            $formFields = $model->getClub()->getSettings()->toArray();            
        }

        $form->populate($formFields);
        $this->view->form = $form;

        $this->view->messages = $flashMessenger->getCurrentMessages();
        $flashMessenger->clearCurrentMessages();
    }
    
    public function performancesAction()
    {
        $chart = new Model_Charts_Charts();
        
        $this->view->headScript()->prependFile('https://www.google.com/jsapi');
        $this->view->stats = $chart->getClubStatsData();
    }
}
