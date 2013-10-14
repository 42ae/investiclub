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
 * Finance ajax controller
 * 
 * Handles Ajax request and form rendering
 * 
 * @author      Alexandre Esser
 * @category    InvestiClub
 * @package     Controller
 */
class Ajax_FinanceController extends Zend_Controller_Action
{
    /**
     * User's instance
     * @var Ivc_Model_Users_User $_user
     */
    
    public function preDispatch()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->redirector('index', 'index', 'default');
        }
    }
    
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('form', 'html')
                    ->addActionContext('transaction', 'json')
                    ->addActionContext('transaction-buy', 'json')
                    ->addActionContext('transaction-delete', 'json')
                    ->addActionContext('list', 'html')
                    ->addActionContext('reevaluate', 'json')
                    ->addActionContext('records', 'json')
                    ->initContext();
    }

    public function indexAction()
    {
        //default action
    }
    
    public function listAction()
    {
        $model = new Model_Portfolio_Portfolio();
        $this->view->portfolio = $model->getData();
    }

    public function reevaluateAction()
    {
        $portfolio = new Model_Portfolio_Portfolio();
        $reevaluationDates = $portfolio->getReevaluationList();
        $date = $this->_getParam('date', $reevaluationDates[0]);
        $stocks = $portfolio->getReevaluationData($date);
        
        $form = new Form_Portfolio_Reevaluate();
        $form->addPortfolioStocks($stocks);
        $form->getElement('date')->setValue($this->_getParam('date'));
        
        $this->view->content = '<form method="post" id="form-reevaluation" action="/finance/reevaluate/date/' . $this->_getParam('date') . '"><div id="reevaluation-list"></div>' . $form->submit . '</form>';
        $this->view->table = $this->view->partial('/finance/partials/_reevaluate-list.phtml', 'default', array('stocks' => $stocks, 'form' => $form));
    }

    public function recordsAction()
    {
        $treasury = new Model_Treasury_Treasury;
        $balanceSheetInfo = $treasury->getTreasuryDates();
        $date = $this->_getParam('date', $balanceSheetInfo['treasuryName']);
        
        $treasuryData = $treasury->getData($date);
        $treasury->setActions($treasuryData);
        $balanceSheetInfo = $treasury->getTreasuryDates();
        
        $members = new Model_Members_Members();
        $treasury = new Model_Treasury_Treasury();
 
        $model = array('members'       => $members->listMembers(), 
						 'treasury'      => $treasuryData, 
						 'balanceSheet'  => $balanceSheetInfo, 
						 'acl'           => null);
        
        $revaluation = array();
        foreach ($treasuryData['events'] as $key => $value) {
            if ($value['ope'] == 'reevaluation') {
                $revaluation[$key] = $value;
                unset($treasuryData['events'][$key]);
            }
        }
        
        $model['revaluation'] = $revaluation;

        $result = array();
	    $result['table'] = $this->view->partial('finance/partials/_balance-sheets-table.phtml', 'default', $model);
	    $result['records'] = $this->view->partial('/finance/partials/_records-table.phtml', 'default', $model);
	    $result['start'] = $balanceSheetInfo['startDate'];
	    $result['end'] = $balanceSheetInfo['endDate'];
	    $result['status'] = $balanceSheetInfo['status'];
	    $result['name'] = $balanceSheetInfo['treasuryName'];
	    $this->view->balancesheet = $result;
    }

    public function transactionDeleteAction()
    {
        $form = new Form_Portfolio_Transaction();
        $form->removeElement('force');
        $form->removeElement('type');
        $form->removeElement('symbol');
        $form->removeElement('currency');
        $form->removeElement('date');
        $form->removeElement('shares');
        $form->removeElement('price');
        $form->removeElement('fees');
        $form->removeElement('submit');            
        
        $data = $this->getRequest()->getPost();
        $form->isValid($data);
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $model = new Model_Portfolio_Portfolio();
            $model->deleteTransaction($data);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
    }
    
    public function transactionAction()
    {
        $form = new Form_Portfolio_Transaction();
        $form->removeElement('symbol');
        $form->removeElement('currency');
        if ($this->_getParam('type') == 'edit') {
            $form->removeElement('date');
        }
            
        $data = $this->getRequest()->getPost();
        $form->isValid($data);
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $model = new Model_Portfolio_Portfolio();
            $model->{"add" . ucfirst($data['type'])}($data);
            $messages = $model->getMessages();
        } else {
            $messages = $form->getMessages();
        }
        $notice = $this->view->notice()->format($messages);
        $this->view->messages = $notice;
    }

    public function transactionBuyAction()
    {
        $form = new Form_Portfolio_Transaction();
        $form->removeElement('stock_id');
        $data = $this->getRequest()->getPost();
        $form->isValid($data);
        
        if ($form->isValid($data)) {
            $data = $form->getValues();
            $model = new Model_Portfolio_Portfolio();
            $model->addBuy($data);
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
        if ($this->_hasParam('transaction')) {
            $form = new Form_Portfolio_Transaction(array('id' => 'form-transaction'));
            $form->removeElement('symbol');
            $form->removeElement('currency');
            $form->removeElement('submit');
            $form->setDefault('stock_id', $this->_getParam('transaction'));
            $this->view->form = $form;
            $this->renderScript("/finance/form.transaction.ajax.phtml");
        } elseif ($this->_hasParam('transaction-edit')) {
            
            $model = new Model_Portfolio_Portfolio;
            $data = $model->getTransacById($this->_getParam('transaction-edit'));

            $form = new Form_Portfolio_Transaction(array('id' => 'form-transaction'));
            $form->removeElement('symbol');
            $form->removeElement('currency');
            $form->removeElement('submit');
            $form->setDefault('stock_id', $this->_getParam('transaction-edit'));
            $form->setDefault('type', 'edit');
            foreach ($data as $field => $value) {
                $form->setDefault($field, $value);
            }
            $form->getElement('date')->setAttrib('disabled', 'disabled');
            $this->view->form = $form;
            $this->renderScript("/finance/form.transaction.ajax.phtml");
        } elseif ($this->_hasParam('transaction-delete')) {
            $form = new Form_Portfolio_Transaction(array('id' => 'form-transaction-delete'));
            $form->setDefault('stock_id', $this->_getParam('transaction-delete'));
            $form->removeElement('force');
            $form->removeElement('type');
            $form->removeElement('symbol');
            $form->removeElement('currency');
            $form->removeElement('date');
            $form->removeElement('shares');
            $form->removeElement('price');
            $form->removeElement('fees');
            $form->removeElement('submit');
            $this->view->form = $form;
            $this->view->showDeleteMessage = true;
            $this->renderScript("/finance/form.transaction.ajax.phtml");
        }
        
    }
}