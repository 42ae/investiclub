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
 * @package		Model
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Enter description here ...
 * 
 * @author		Jonathan Hickson
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Treasury
 */

class Model_Treasury_TreasuryAction
{
    private $_treasury;
    private $_status;
    private $_acl;
    
    public function __construct (Model_Treasury_Treasury $treasury)
    {
        $this->_treasury = $treasury;
        $this->_acl = Zend_Registry::get('Ivc_Acl');
    }
    
    private function checkAcl($actions)
    {
        $out = array();
        foreach($actions as $action)
        {
            if ($this->_acl->ivcAllowed($this->_treasury, $action))
                $out[] = $action;
        }
        return ($out);
    }
    
    private function setAction_credit(&$event)
    {
        if ($this->_status !== 'closed')
            $event['action'] = $this->checkAcl(array('delCredit', 'editCredit'));
    }
    
    private function setAction_debit(&$event)
    {
        if ($this->_status !== 'closed')
            $event['action'] = array('delDebit', 'editDebit');
    }
    
    private function setAction_buy(&$event)
    {
        if ($this->_status !== 'closed')
            $event['action'] = array('delBuy', 'editBuy');
    }
    
    private function setAction_sell(&$event)
    {
        if ($this->_status !== 'closed')
        {
            $event['action'] = array('delSell', 'editSell');
            if (isset($event['gain']) && $event['gain'] > 0) // Should not need isset
                $event['action'][] = 'editSellDistribution';
        }
    }
    
    private function setAction_reevaluation(&$event)
    {
        if ($this->_status !== 'closed')
            $event['action'] = array('delReevaluation', 'editReevaluation');
    }
    
    private function setAction_demission(&$event)
    {
        if ($this->_status !== 'closed')
            $event['action'] = array('delDemission', 'editDemission');
    }
    
    private function setAction_dividend(&$event)
    {
        
    }
    
    public function setActions (&$data)
    {
        $this->_status = $data['status'];
        //echo "Set Actions<br />";
        foreach ($data['events'] as $key => $val)
        {

            $this->{'setAction_' . $val['ope']}($data['events'][$key]);
            //
        }
        //echo "END Set Actions<br />";
    }

    
    
}
?>