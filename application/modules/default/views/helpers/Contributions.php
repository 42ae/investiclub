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
class Zend_View_Helper_Contributions extends Zend_View_Helper_Abstract
{
    public function contributions()
    {
        return $this;
    }
	
    public function getFullName($member)
    {
        return $member->getUser()->first_name . ' ' . $member->getUser()->last_name;
	}
    
	public function getStatus($contribution, $member)
	{
	    $html = '';
        if ($contribution['status'] === 'paid')
            $html = '<span class=\"block tip center-text\" title=\"' . $this->getFullName($member) . ' a payé(e) sa cotisation\"><img class=\"icon\" alt=\"Payé\" src=\"/assets/img/icons/success.png\" /></span>';
        else if ($contribution['status'] === 'unpaid')
            $html = '<span class=\"block tip center-text\" title=\"Impayé\"><img class=\"icon\" alt=\"Impayé\" src=\"/assets/img/icons/clock.png\" /></span>';
        return $html;
	}

	public function getDashboard($balanceSheet, $contribution, $member, $acl)
	{
	    $html = '';
		if ($balanceSheet['status'] !== 'closed' AND $contribution['status'] === 'unpaid') // AND in_array('addContribution', $acl) CHECK ACL
		{
	        $html .= '<a class=\"add-contribution\" href=\"/ajax/members/form/add-contribution/' . $member->member_id . '/date/' . $balanceSheet['treasuryName'] . '\">' . 
    	    	     '<img class=\"icon tip\" title=\"Ajouter une cotisation\" alt=\"Ajouter une cotisation\" src=\"/assets/img/icons/add.png\" /></a>';
	    }
        if ($balanceSheet['status'] !== 'closed' AND $contribution['status'] === 'paid')
        {
	        $html .= '<a class=\"edit-contribution\" href=\"/ajax/members/form/edit-contribution/' . $member->member_id . '/date/' . $balanceSheet['treasuryName'] . '\">' . 
    	    	     '<img class=\"icon tip\" title=\"Editer cette cotisation\" alt=\"Editer cette cotisation\" src=\"/assets/img/icons/edit.png\" /></a>';
        }
	    if ($balanceSheet['status'] !== 'closed' AND $contribution['status'] === 'paid')
	    {
            $html .= '<a class=\"delete-contribution\" href=\"/ajax/members/form/delete-contribution/' . $member->member_id . '/date/' . $balanceSheet['treasuryName'] . '\">' . 
	    	     '<img class=\"icon tip\" title=\"Supprimer cette cotisation\" alt=\"Supprimer cette cotisation\" src=\"/assets/img/icons/delete.png\" /></a>';
	    }
	    if ($balanceSheet['status'] !== 'closed' AND $contribution['status'] === 'unpaid')
	    {
            $html .= '<a class=\"send-reminder\" href=\"/ajax/members/form/send-reminder/' . $member->member_id . '/date/' . $balanceSheet['treasuryName'] . '\">' . 
	    	     '<img class=\"icon tip\" title=\"Envoyer un rappel\" alt=\"Envoyer un rappel\" src=\"/assets/img/icons/send-reminder.png\" /></a>';
	    }
	    return $html;
	}
	
}
