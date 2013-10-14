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
class MembersController extends Zend_Controller_Action
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
        $this->_helper->redirector('list', 'members', 'default');
    }

    public function listAction()
    {
        $model = new Model_Members_Members();
        $addMember = new Form_Members_AddMember();

        $this->view->headScript()->prependFile('https://www.google.com/jsapi');
        $this->view->formAddMember = $addMember;
        $this->view->pendingMembers = $model->listPendingMembers();
        $this->view->members = $model->listMembers();
        $this->view->acl = $model->getAllowedAction();
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }
    
    public function sharesAction()
    {
        $model = new Model_Members_Members();
        $treasury = new Model_Treasury_Treasury();
        
        $this->view->headScript()->prependFile('https://www.google.com/jsapi');
        $this->view->members = $model->listMembers();
        $this->view->stats = $treasury->getMembersStats();
    }
    
    public function contributionAction()
    {
        $members = new Model_Members_Members();
        $treasury = new Model_Treasury_Treasury();
        $contributions = new Model_Treasury_Contribution();

        if ($this->_hasParam('date')) {
            $date = $this->_getParam('date');
        } else {
            $date = $treasury->getTreasuryDates();
            $date = $date['treasuryName'];
        }
        
        
        $listContributions = $contributions->getContributions(array('treasuryName' => $date));
        $balanceSheetInfo = $contributions->getTreasury()->getTreasuryDates();
        $listMembers = $members->listMembers();
        
        if ($balanceSheetInfo['status'] == 'ongoing' OR $balanceSheetInfo['status'] == 'validation' OR $balanceSheetInfo['status'] == 'firstValidation') {
            $memberToContributeLeft = false;
            foreach ($listContributions as $memberId) {
                if ($memberId['status'] == 'unpaid') {
                    $memberToContributeLeft = true;
                }
            }
            if ($memberToContributeLeft === true) {
                $addAllContribution = new Form_Members_AddAllContribution();
                $addAllContribution->addMembersFields($listMembers, $listContributions, $balanceSheetInfo);
                $this->view->formAddAllContribution = $addAllContribution;
            }
        }
        
        $this->view->headScript()->prependFile('https://www.google.com/jsapi');
        $this->view->members = $listMembers;
        $this->view->contributions = $listContributions;
        $this->view->balanceSheet = $balanceSheetInfo;
        $this->view->balanceSheetList = $treasury->getTreasuryListByYear();
        $this->view->acl = $members->getAllowedAction();
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }
    
    public function makeAdminAction()
    {
        $form = new Form_Members_MakeAdmin();
        $data = $this->getRequest()->getPost();
        if ($form->isValid($data)) {
            $model = new Model_Members_Members();
            $model->makeAdmin($form->getValue('id'));
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $this->_helper->flashMessenger->addMessage($messages);
        $this->_helper->redirector('list', 'members', 'default');
    }
}

