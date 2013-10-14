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
 * @package		View
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Portfolio View Helper
 * 
 * Renders a portoflio
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		View
 * @subpackage	Helper
 */
class Zend_View_Helper_Records extends Zend_View_Helper_Abstract
{
    public function records()
    {
        return $this;
    }
    
    public function getOperation($data)
    {
        $html = '';
        $gateway = new Ivc_Model_Clubs_Gateway;
        
        switch ($data['ope']) {
            case "buy":
                $html = 'Achat : ' . $data['shares'] . ' parts de ' . $data['symbol'];
                break;
            case "sell":
                $html = 'Vente : ' . $data['shares'] . ' parts de ' . $data['symbol'];
                break;
            case "credit":
                $member = $gateway->fetchMemberById($data['club_user_id']);
                $memberName = $member->getUser()->first_name . ' ' . $member->getUser()->last_name;
                $html = 'Versement : ' . $memberName;
                break;
            case "debit":
                $member = $gateway->fetchMemberById($data['club_user_id']);
                $memberName = $member->getUser()->first_name . ' ' . $member->getUser()->last_name;
                $html = 'Retrait : ' . $memberName;
                break;
            case "demission":
                $member = $gateway->fetchMemberById($data['club_user_id']);
                $memberName = $member->getUser()->first_name . ' ' . $member->getUser()->last_name;
                $html = 'Départ de ' . $memberName;
                break;
        }
        return $html;
    }

    public function getDebit($data)
    {
        $html = '';
        switch ($data['ope']) {
            case "buy":
                $html = $data['price'] * $data['shares'];
                break;
            case "debit":
                $html = $data['value'];
                break;
            case "demission":
                $html = $data['value'] . ' (Frais : ' . $data['fees'] . ")";
                break;
        }
        return $html;
    }
    
    public function getCredit($data)
    {
        $html = '';
        switch ($data['ope']) {
            case "sell":
                $html = $data['value'] . ' (' . round($data['profit'], 2) . ')';
                break;
            case "credit":
                $html = $data['value'];
                break;
            case "dividend":
                $html = $data['price'] * $data['shares'];
                break;
        }
        return $html;
    }

    public function getFullName($member)
    {
        return $member->getUser()->first_name . ' ' . $member->getUser()->last_name;
	}
	
	public function getProfileLink($member)
    {
        if ($member->isPending() OR $member->isUnregistered()) {
            return $this->getFullName($member);
        }
        
        $urlOptions = array('controller' => 'users',
                            'action' => 'view',
                            'id' => $member->getUser()->user_id);
        
        $url = $this->view->url($urlOptions, 'users', true);
        return '<a href=\"'. $url . '\">' . $this->getFullName($member) . '</a>';
	}
    
	public function showDashboard($members, $acl)
	{
	    $bool = false;
	    foreach ($members as $member) {
	        if ($this->getDashboard($member, $acl)) {
	            $bool = true;
	        }
	    }
	    return $bool;
	}
	
}
