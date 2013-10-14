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
class Zend_View_Helper_Members extends Zend_View_Helper_Abstract
{
    public function members()
    {
        return $this;
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
    
	public function getStatus($member)
	{
	    $html = '';
        if ($member->isPending())
            $html = '<span class=\"block tip center-text\" title=\"En attente de validation\"><img class=\"icon\" alt=\"Pending\" src=\"/assets/img/icons/clock.png\" /></span>';
        elseif ($member->isActive())
            $html = '<span class=\"block tip center-text\" title=\"' . $this->getFullName($member) . ' est actif\"><img class=\"icon\" alt=\"Success\" src=\"/assets/img/icons/success.png\" /></span>';
        elseif ($member->isUnregistered())
            $html = '<span class=\"block tip center-text\" title=\"Membre non-enregistré\"><img class=\"icon\" alt=\"Unregistered\" src=\"/assets/img/icons/cancel.png\" /></span>';
        return $html;
	}
	
	public function getStatusCss($member)
	{
	    $css = 'background-color:';
	    if ($member->isPending())
            $css .= '#FFBCAF';
        elseif ($member->isActive())
            $css .= '#BFDF99';
        elseif ($member->isUnregistered())
            $css .= '#FFBCAF';
        return $css;
	}

	public function getDashboard($member, $acl)
	{
	    $html = '';
	    if (in_array('editMember', $acl))
	    {
    	    $html .= '<a class=\"edit-member\" href=\"/ajax/members/form/edit/' . $member->member_id . '\">' . 
    	    		'<img class=\"icon tip\" title=\"Modifier\" alt=\"Edit\" src=\"/assets/img/icons/edit.png\" /></a>';
	    }
		if (in_array('addEmail', $acl) AND $member->isUnregistered())
		{
	        $html .= '<a class=\"add-email\" href=\"/ajax/members/form/add-email/' . $member->member_id . '\">' . 
    	    	     '<img class=\"icon tip\" title=\"Ajouter un e-mail\" alt=\"Add an email\" src=\"/assets/img/icons/add-email.png\" /></a>';
	    }
		if (in_array('makeAdmin', $acl) AND $member->isActive() AND !$member->isPending() AND $member->admin == false)
		{
	        $html .= '<a class=\"make-admin\" href=\"/ajax/members/form/make-admin/' . $member->member_id . '\">' . 
    	    	     '<img class=\"icon tip\" title=\"Nommer administrateur du club\" alt=\"Make admin\" src=\"/assets/img/icons/make-admin.png\" /></a>';
	    }
	    if (in_array('deleteMember', $acl) AND $member->member_id != Ivc::getCurrentMember()->member_id)
	    {
            $html .= '<a class=\"delete-member\" href=\"/ajax/members/form/delete/' . $member->member_id . '\">' . 
	    	     '<img class=\"icon tip\" title=\"Supprimer\" alt=\"Delete\" src=\"/assets/img/icons/delete.png\" /></a>';
	    }	    return $html;
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
