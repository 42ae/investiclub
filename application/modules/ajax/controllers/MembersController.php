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
 * Members ajax controller
 * 
 * Handles Ajax request and form rendering
 * 
 * @author      Alexandre Esser
 * @category    InvestiClub
 * @package     Controller
 */
class Ajax_MembersController extends Zend_Controller_Action
{
    /**
     * User's instance
     * @var Ivc_Model_Users_User $_user
     */
    protected $_gateway;
    protected $_member;
    
    public function preDispatch()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->redirector('index', 'index', 'default');
        }
    }
    
    public function init()
    {
        $this->_gateway = new Ivc_Model_Clubs_Gateway();
        $this->_member = $this->_gateway->fetchMember(Ivc::getCurrentUser());
        
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('list', 'html') // members list
                    ->addActionContext('edit', 'json')
                    ->addActionContext('delete', 'json')
                    ->addActionContext('add', 'json')
                    ->addActionContext('add-email', 'json')
                    ->addActionContext('form', 'html')
                    ->addActionContext('contributions', 'html') // contributions
                    ->addActionContext('add-all-contribution', 'json')
                    ->addActionContext('add-contribution', 'json')
                    ->addActionContext('edit-contribution', 'json')
                    ->addActionContext('delete-contribution', 'json')
                    ->addActionContext('send-reminder', 'json')
                    ->initContext();
    }

    public function indexAction()
    {
        //default action
    }
    
    public function listAction()
    {
        $model = new Model_Members_Members();
        $this->view->members = $model->listMembers();
        $this->view->pendingMembers = $model->listPendingMembers();
        $this->view->acl = $model->getAllowedAction();
    }

    public function contributionsAction()
    {
        $members = new Model_Members_Members();

        $date = $this->_getParam('date', Zend_Date::now()->toString("YYYY-MM-dd"));
        $contributions = new Model_Treasury_Contribution(array('treasuryName' => $date));

        $balanceSheet = $contributions->getTreasury()->getTreasuryDates();
        
        $this->view->date = $date;
        $this->view->contributions = $contributions->getContributions(array('treasuryName' => $date));
        $this->view->balanceSheet = $balanceSheet;
        $this->view->members = $members->listMembers();
        $this->view->acl = $members->getAllowedAction();
        
    }
    
    public function addContributionAction()
    {
        $date = $this->_getParam('date', Zend_Date::now()->toString("YYYY-MM-dd"));
        $model = new Model_Treasury_Contribution(array('treasuryName' => $date));

        $balanceSheetInfo = $model->getTreasury($date)->getTreasuryDates();
        $form = new Form_Members_AddContribution(array('balanceSheetInfo' => $balanceSheetInfo));
        
        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $model->addContribution($data);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
    }

    public function editContributionAction()
    {
        $date = $this->_getParam('date', Zend_Date::now()->toString("YYYY-MM-dd"));
        $model = new Model_Treasury_Contribution(array('treasuryName' => $date));

        $balanceSheetInfo = $model->getTreasury($date)->getTreasuryDates();
        $form = new Form_Members_AddContribution(array('balanceSheetInfo' => $balanceSheetInfo));
        
        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $model->editContribution($data);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
    }
    
    public function editAction()
    {
        $id = (int) $this->_getParam('id');
        $form = new Form_Members_EditMember();
        $form->addMemberRelatedElements($id);
        $data = $this->getRequest()->getPost();
        $form->isValid($data);
        
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $model = new Model_Members_Members();
            $model->editMember($id, $data);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
    }
    
    public function deleteContributionAction()
    {
    	
        $date = $this->_getParam('date', Zend_Date::now()->toString("YYYY-MM-dd"));
        $model = new Model_Treasury_Contribution(array('treasuryName' => $date));
        
        $balanceSheetInfo = $model->getTreasury($date)->getTreasuryDates();
        $form = new Form_Members_DeleteContribution(array('balanceSheetInfo' => $balanceSheetInfo));
        
        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
        	
            $model->delContribution($form->getValue('id'), $balanceSheetInfo['startDate'], $balanceSheetInfo['endDate']);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
    }

    public function sendReminderAction()
    {
        $date = $this->_getParam('date', Zend_Date::now()->toString("YYYY-MM-dd"));
        $model = new Model_Treasury_Contribution(array('treasuryName' => $date));

        $balanceSheetInfo = $model->getTreasury($date)->getTreasuryDates();
        
        $form = new Form_Members_SendReminder(array('balanceSheetInfo' => $balanceSheetInfo));
        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
            $model->sendReminder($form->getValue('id'), $balanceSheetInfo['startDate'], $balanceSheetInfo['endDate']);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
    }

    public function addAction()
    {
        $form = new Form_Members_AddMember();
        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $model = new Model_Members_Members();
            $model->addMember($data);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
    }

    public function addAllContributionAction()
    {
        $form = new Form_Members_AddAllContribution();

        $members = new Model_Members_Members();
        $model = new Model_Treasury_Contribution();
        $treasury = new Model_Treasury_Treasury();

        $listMembers = $members->listMembers();

        if ($this->_hasParam('date')) {
            $date = $this->_getParam('date');
        } else {
            $date = $treasury->getTreasuryDates();
            $date = $date['treasuryName'];
        }
        
        $listContributions = $model->getContributions(array('treasuryName' => $date));
        $balanceSheetInfo = $model->getTreasury()->getTreasuryDates();
        
        $form->addMembersFields($listMembers, $listContributions, $balanceSheetInfo);

        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $model->addContributionList($data);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
    }

    public function addEmailAction()
    {
        $form = new Form_Members_AddEmail();
        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $model = new Model_Members_Members();
            $model->addEmail($data);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
    }

    public function deleteAction()
    {
        $form = new Form_Members_Delete();
        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
            $model = new Model_Members_Members();
            $model->deleteMember($form->getValue('id'));
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
    }

    public function formAction()
    {
        if ($this->_hasParam('edit')) {
            $id = (int) $this->_getParam('edit');
            $form = new Form_Members_EditMember(array('id' => 'form-edit-member'));
            $form->addMemberRelatedElements($id);
            $this->view->form = $form;
            $this->renderScript("/members/form.edit.ajax.phtml");
        }
        
        if ($this->_hasParam('add-email')) {
            $form = new Form_Members_AddEmail(array('id' => 'form-add-email'));
            $form->setDefault('id', $this->_getParam('add-email'));
            $this->view->form = $form;
            $this->renderScript("/members/form.add-email.ajax.phtml");
        }
        
        if ($this->_hasParam('make-admin')) {
            $form = new Form_Members_MakeAdmin(array('id' => 'form-make-admin', 'action' => '/members/make-admin/'));
            $form->setDefault('id', $this->_getParam('make-admin'));
            $this->view->form = $form;
            $this->renderScript("/members/form.make-admin.ajax.phtml");
        }
        
        if ($this->_hasParam('delete')) {
            $form = new Form_Members_Delete(array('id' => 'form-delete-member'));
            $form->setDefault('id', $this->_getParam('delete'));
            $this->view->form = $form;
            $this->renderScript("/members/form.delete.ajax.phtml");
        }

        if ($this->_hasParam('delete-contribution')) {
            $date = $this->_getParam('date', Zend_Date::now()->toString("YYYY-MM-dd"));
            $model = new Model_Treasury_Contribution(array('treasuryName' => $date));
            $balanceSheetInfo = $model->getTreasury($date)->getTreasuryDates();
            
            $form = new Form_Members_DeleteContribution(array('id' => 'form-delete-contribution',
                                                              'balanceSheetInfo' => $balanceSheetInfo));
            
            $form->setDefault('id', $this->_getParam('delete-contribution'));
            $form->setDefault('date', $balanceSheetInfo['treasuryName']);
            $this->view->form = $form;
            $this->renderScript("/members/form.delete-contribution.ajax.phtml");
        }

        if ($this->_hasParam('send-reminder')) {
            $date = $this->_getParam('date', Zend_Date::now()->toString("YYYY-MM-dd"));
            $model = new Model_Treasury_Contribution(array('treasuryName' => $date));
            $balanceSheetInfo = $model->getTreasury($date)->getTreasuryDates();

            $form = new Form_Members_SendReminder(array('id' => 'form-send-reminder',
                                                        'balanceSheetInfo' => $balanceSheetInfo));
            $form->setDefault('id', $this->_getParam('send-reminder'));
            $form->setDefault('date', $balanceSheetInfo['treasuryName']);
            $this->view->form = $form;
            $this->renderScript("/members/form.send-reminder.ajax.phtml");
        }

    
        if ($this->_hasParam('add-contribution')) {
            $date = $this->_getParam('date', Zend_Date::now()->toString("YYYY-MM-dd"));
            $model = new Model_Treasury_Contribution(array('treasuryName' => $date));
            $balanceSheetInfo = $model->getTreasury($date)->getTreasuryDates();
            
            $form = new Form_Members_AddContribution(array(
            			'id' => 'form-add-contribution',
                        'balanceSheetInfo' => $balanceSheetInfo)
            );
            $form->setDefault('id', $this->_getParam('add-contribution'));
            $form->setDefault('date', $balanceSheetInfo['treasuryName']);
            $this->view->form = $form;
            $this->renderScript("/members/form.add-contribution.ajax.phtml");
        }

        if ($this->_hasParam('edit-contribution')) {
            $date = $this->_getParam('date', Zend_Date::now()->toString("YYYY-MM-dd"));
            $model = new Model_Treasury_Contribution(array('treasuryName' => $date));
            $balanceSheetInfo = $model->getTreasury($date)->getTreasuryDates();

            $form = new Form_Members_AddContribution(array(
            			'id' => 'form-edit-contribution',
                        'balanceSheetInfo' => $balanceSheetInfo)
            );
            
            $listContributions = $model->getContributions();
            $memberContribution = $listContributions[$this->_getParam('edit-contribution')];
            
            $form->setDefault('id',                $this->_getParam('edit-contribution'));
            $form->setDefault('date',     $balanceSheetInfo['treasuryName']);
            $form->setDefault('amount',            $memberContribution['value']);
            //$form->setDefault('contribution_date', $memberContribution['lastContribution']);
            $this->view->form = $form;
            $this->renderScript("/members/form.edit-contribution.ajax.phtml");
        }
    
    }
}