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
 * Finance controller
 * 
 * This controller contains every action relative to the financial
 * management.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Controller
 */
class FinanceController extends Zend_Controller_Action
{
    
    public function preDispatch()
    {
        $this->_helper->navigation()->renderBreadcrumbs();
        $this->_helper->navigation()->renderSubMenu();
    }

    /**
     * Init Ajax context and redirect requests to actions according
     * to the information sent in the HTTP request header.
     * 
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        $this->view->jQuery()->uiEnable();
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('transaction', 'json')
                    ->addActionContext('list', 'html')
                    ->initContext();
    }

    /**
     * Redirect to the list action
     */
    public function indexAction()
    {
        $model = new Model_Treasury_Treasury();
        $model->checkAcl('list');
        $this->_helper->redirector('list', 'finance', 'default');
    }
    
    public function listAction()
    {
        $form = new Form_Portfolio_Transaction();
        $model = new Model_Portfolio_Portfolio();
        $portfolio = $model->getData();
        
        $this->view->form = $form;
        $this->view->portfolio = $portfolio;
    }

    public function reevaluateAction()
    {
        $portfolio = new Model_Portfolio_Portfolio();
        $reevaluationDates = $portfolio->getReevaluationList();

        if (empty($reevaluationDates) == false) {
            $date = $this->_getParam('date', $reevaluationDates[0]);
            
            $form = new Form_Portfolio_Reevaluate();
            $form->addPortfolioStocks($portfolio->getReevaluationData($date));
            $form->getElement('date')->setValue($date);
            
            $request = $this->getRequest();
            if ($request->isPost()) {
                $_POST['date'] = $date;
                if ($form->isValid($request->getPost())) {
                    $portfolio->addReevaluation($request->getPost());
                    $messages = $portfolio->getMessages();
                } else {
                    $messages = $form->getMessages();
                }
                $this->_helper->flashMessenger->addMessage($messages);
                $this->_helper->flashMessenger->addMessage($messages);
                $this->_helper->redirector('reevaluate', 'finance', 'default');
            }
            
            $this->view->form = $form;
            $this->view->stocks = $portfolio->getReevaluationData($date);
            $this->view->date = $date;
            $this->view->portfolio = $portfolio;
            $this->view->headScript()->prependFile('https://www.google.com/jsapi');
        } else {
            // everything is ok - no reevaluation to do
        }
        
        $this->view->reevaluationDates = $reevaluationDates;
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    public function recordsAction()
    {
        $treasury = new Model_Treasury_Treasury;
        $balanceSheetInfo = $treasury->getTreasuryDates();
 
        if ($this->_hasParam('date')) {
            $date = $this->_getParam('date');
        } else {
            $date = $balanceSheetInfo['treasuryName'];
        }
        
        $treasuryData = $treasury->getData($date);
        $treasury->setActions($treasuryData);
        $balanceSheetInfo = $treasury->getTreasuryDates();
        
        $model = new Model_Members_Members();
        $treasury = new Model_Treasury_Treasury();
        
        $this->view->members = $model->listMembers();
        $this->view->headScript()->prependFile('https://www.google.com/jsapi');
        $this->view->balanceSheet = $balanceSheetInfo;
        
        $request = $this->getRequest();
        if ($request->isGet() AND $this->_getParam('name')) {
            $name = $this->_getParam('name');
            $treasury->validatePeriod($name);
            $this->view->messages = $treasury->getMessages();
        }
        
        $this->view->balanceSheetList = $treasury->getTreasuryListByYear();
        $this->view->treasury = $treasuryData;
    }
    
    public function transactionsAction()
    {
        $bilan_date = $this->getRequest()->getParam('date');
        if (!$bilan_date)
            $bilan_date = '2011-04-01'; // just for test
        $treasury = new Model_Treasury_Treasury("2011-04-28");
        $treasury->setClubCreationDate("2010-12-24");
        $treasury->setDebugMode(false);
        //$treasury->setDebugMode(true);
        // Display bilan's list
        foreach ($treasury->getTreasuryList() as $key => $val)
        {
            echo  '<a href="' .  $this->view->url(
            array('controller'=>'portfolio',
                  'action' => 'transactions',
                    'date' => $key),
            'default',
            true) . '">' . "bilan: $key" . '</a><br />';
        };
        echo  '<a href="' .  $this->view->url(
            array('controller'=>'portfolio',
                  'action' => 'transactions'),
            'default',
            true) . '">' . "bilan: Current" . '</a><br /><br />';
            
        // Display selected bilan from date (works within intervals)
        $data = $treasury->getData($bilan_date); // Get bilan
        $treasury->setActions($data); // Set actions for this bilan
        //Zend_Debug::Dump($data);
        echo "<H1>Bilan $bilan_date (" . $data['status'] . ") | cotisation: " . (($data['flag_cotisation']) ? "OK" : "KO") . " reevaluation: " . ($data['flag_revaluation'] ? "OK" : "KO") . "</H1>";
        echo "<table width=\"100%\"><tr>";
        echo "<td>date</td>";
        echo "<td>operation</td>";
        echo "<td>debit</td>";
        echo "<td>credit</td>";
        echo "<td>solde</td>";
        echo "<td>Actif</td>";
        echo "<td>Passif</td>";
        echo "<td>observations</td>";
        echo "<td>liens</td>";
        echo "</tr>";
        foreach ($data['events'] as $key => $val)
        {
            echo "<tr>";
            echo "<td>$key</td>";
            echo "<td>" . $val['ope'] . "</td>";
            echo "<td>"; if ($val['ope'] === "debit") echo $val['value']; if ($val['ope'] === "buy") echo ($val['price'] * $val['shares']) ; echo "</td>";
            echo "<td>"; if ($val['ope'] === "credit") echo $val['value']; if ($val['ope'] === "sell") echo $val['value']; echo "</td>";
            echo "<td>" ; if ($val['ope'] !== "reevaluation") echo $val['solde'] ; echo "</td>";
            echo "<td>"; if ($val['ope'] === "reevaluation")
                            echo "Before<br />" . "PF: "
                                . $val['old_pf'] . "<br />Solde: " . $val['solde']
                                . "<br />= " . ($val['old_pf'] + $val['solde'])
                                ."<br />After<br />" . "PF: "
                                . $val['new_pf'] . "<br />Solde: " . $val['solde']
                                . "<br />= " . ($val['new_pf'] + $val['solde']);
            echo "</td>";
            echo "<td>"; if ($val['ope'] === "reevaluation")
                            echo "Before<br />" . "Capital: "
                                . $val['old_capital']
                                . "<br />= " . $val['old_capital'] . "<br />"
                                ."<br />After<br />" . "Capital: "
                                . $val['old_capital'] . "<br /> reev: " . $val['value']
                                . "<br />= " . $val['new_capital'];
            echo "</td>";
            echo "<td>" ; if (isset($val['comment'])) echo $val['comment']; echo "</td>";
            echo "<td>" ;if (isset($val['action'])) foreach($val['action'] as $ac) echo "$ac "; echo "</td>";
            echo "</tr>"; 
        }
                echo "<td>Fin du mois</td>";
        echo "<td>Bilan fin du mois</td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td>Actif<br />" . "PF: "
        . $data['pf'] . "<br />Solde: " . $data['solde']
        . "<br />= " . ($data['pf'] + $data['solde']) . "</td>";
        echo "<td>Passif<br />Capital: " . $data['capital'] . "<br />(Profits : " . $data['profit'] . ")<br />= " . $data['capital'] . "</td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "</tr></table>";
    }
}

