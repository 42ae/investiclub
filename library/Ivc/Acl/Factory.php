<?php
/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category	Ivc
 * @package		Ivc_Acl
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Enter description here ...
 * 
 * @author		Jonathan Hickson
 * @category	Ivc
 * @package		Ivc_Acl
 * @subpackage	Factory
 */
class Ivc_Acl_Factory
{
    private $_user = null;
    private $_userId = null;
    private $_clubId = null;
    private $_memberId = null;
    
	private $ivcRole = null;
	private $clubRole = Ivc_Acl::GUEST;
	
	public function __construct()
	{
	    $this->_user = Ivc::getCurrentUser();
	    $this->_userId = $this->_user->getRoleId();
        
	    if (Ivc_Auth::isLogged()) {
            if ($this->_user->hasClub()) {
                $gateway = new Ivc_Model_Clubs_Gateway();
                $member = $gateway->fetchMember($this->_user, 'active');
                $this->_clubId = $member->getClub()->club_id;
                $this->clubRole = $member->role . ":";
                $this->ivcRole = ($member->admin) ? Ivc_Acl::CLUB_ADMIN : null;
            } else {
                $this->clubRole = Ivc_Acl::USER;
            } 
        }
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function getAcl()
	{
		// Check infos ?
		$cache = Ivc_Cache::getInstance();
        if (42 || ($acl = $cache->load(Ivc_Cache::SCOPE_USER, 'acl')) === false)
        {
        	$acl = new Ivc_Acl($this->_userId, $this->_memberId, $this->_clubId);
 			if ($this->ivcRole) // Is admin or IvcAdmin
 			{
 				// Insert IVC Role into Club role tree
 				$acl->removeRole($this->ivcRole . $this->_clubId); // remove for replacement
 				$acl->addRole(new Zend_Acl_Role($this->ivcRole . $this->_clubId), $this->clubRole . $this->_clubId); // insert ivcrole in tree
			    $acl->addRole(new Zend_Acl_Role($this->_userId), $this->ivcRole . $this->_clubId);
 			}
 			else
 			{
 			    if ($this->_clubId) // Is Member of a club
 				    $acl->addRole(new Zend_Acl_Role($this->_userId), $this->clubRole . $this->_clubId);
 				else if (is_numeric($this->_userId))// Is User
 					$acl->addRole(new Zend_Acl_Role($this->_userId), $this->clubRole);
 				// Guest already set so do nothing
 			}
 			// Cached after the navigation
        }
        return $acl;
	}
	
    
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $acl
	 * @param unknown_type $role
	 * @param unknown_type $clubRole
	 */
	static function setDynAcl($acl, $resource, $role = null)
	{
	    if (!$role)
	        $role = Ivc::getCurrentUser()->getRoleId();
		// Get Dyn Acl from database
        if (($rowSet = $acl->getDynAclStore()) === null)
        {
		    $dba = Zend_Db_Table::getDefaultAdapter();
		    $select = $dba->select()
		            ->from('user_permissions')
		            ->joinUsing('permissions', 'permission_id')
		            ->where('user_id = ?', $role);
		    $rowSet = $dba->fetchAll($select);
		    $acl->setDynAclStore($rowSet);
        }

        if ($rowSet)
        {
            $parentRole = $acl->getParentRole($role);
		    foreach ($rowSet as $row)
		    {
		        if ($resource->getResourceId() == $row['resource'])
		        {
		            if (!$acl->has($row['resource']))
		                $acl->add(new Zend_Acl_Resource($row['resource']));
		                  
		            if ($parentRole === Ivc_Acl::CLUB_ADMIN || $parentRole === Ivc_Acl::IVC_ADMIN) // Don't override admin privilege
		            {
			            if ($row['is_allowed'] || $acl->isAllowed($parentRole, $row['resource'], $row['privilege']))
				            $acl->allow($role, $row['resource'], $row['privilege']);
			            else
				            $acl->deny($role, $row['resource'], $row['privilege']);
		            }
		            else
		            {
		                if ($row['is_allowed'])
				            $acl->allow($role, $row['resource'], $row['privilege']);
			            else
				            $acl->deny($role, $row['resource'], $row['privilege']);
		            }
		        }
		    }
		}
	}
}
