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
 * ACL is an object that tells a computer operating system which access 
 * rights each user has to a particular object, such as a page, a file 
 * directory or an individual file.
 * 
 * @author		Jonathan Hickson
 * @category	Ivc
 * @package		Ivc_Acl
 */

class Ivc_Acl extends Zend_Acl
{
    // clubRole
	const GUEST = 'guest';
    const USER = 'user:';
    const TEACHER = 'teacher:';

    const CLUB_MEMBER = 'member:';
    const CLUB_PRESIDENT = 'president:';
    const CLUB_SECRETARY = 'secretary:';
    const CLUB_TREASURER = 'treasurer:';
    const CLUB_ADMIN = 'club-admin:';
    // ivcRole
    const IVC_ADMIN = 'ivc-admin:';
    
    private $_role;
    private $_userId;
    private $_clubId;
    private $_memberId;
    private $_dynAclStore = null;
    
    /**
     * 
     * Enter description here ...
     */
	public function __construct($userId, $memberId, $clubId)
	{
		$this->_userId = $userId;
		$this->_memberId = $memberId;
		$this->_clubId = $clubId;
		$this->initRole();
		//$this->initResources($userId, $memberId, $clubId);
		$this->_role = $userId;
		//$this->setGuestAcl();
        //$this->setUserAcl();
		//$this->setMemberAcl();
	}
	
	public function setDynAclStore($data)
	{
	    $this->_dynAclStore = $data;
	    return ($this);
	}
    public function getDynAclStore()
	{
	    return ($this->_dynAclStore);
	}
		
	/**
	 * 
	 * Enter description here ...
	 */
	private function initRole()
	{
		$userId = $this->_userId;
		$memberId = $this->_memberId;
		$clubId = $this->_clubId;
		$this->addRole(new Zend_Acl_Role(self::GUEST));
		$this->addRole(new Zend_Acl_Role(self::USER), self::GUEST);

		if ($clubId)
		{
			$this->addRole(new Zend_Acl_Role(self::CLUB_MEMBER . $clubId), self::USER);
			$this->addRole(new Zend_Acl_Role(self::CLUB_PRESIDENT . $clubId), self::CLUB_MEMBER . $clubId);
			$this->addRole(new Zend_Acl_Role(self::CLUB_SECRETARY . $clubId), self::CLUB_MEMBER . $clubId);
			$this->addRole(new Zend_Acl_Role(self::CLUB_TREASURER . $clubId), self::CLUB_MEMBER . $clubId);
			$this->addRole(new Zend_Acl_Role(self::CLUB_ADMIN . $clubId), self::GUEST);
			//$this->addRole(new Zend_Acl_Role(self::TEACHER), self::CLUB_ADMIN . $clubId); // multiclub admin for schools
		}
		$this->addRole(new Zend_Acl_Role(self::IVC_ADMIN), self::GUEST); // IVC admin
	}
	
	public function allow($roles = null, $resources = null, $privileges = null, Zend_Acl_Assert_Interface $assert = null)
    {
    	if (!$this->hasRole($roles))
    	{
    		/* TODO : go eat burger at HD Dinner - no tree construction, just linear linkage */
    		if (!$this->hasRole("distant"))
    			$this->addRole(new Zend_Acl_Role("distant"), self::GUEST);
    		$this->addRole($roles, "distant");
    	}
        return $this->setRule(self::OP_ADD, self::TYPE_ALLOW, $roles, $resources, $privileges, $assert);
    }
	
	public function deny($roles = null, $resources = null, $privileges = null, Zend_Acl_Assert_Interface $assert = null)
    {
    	if (!$this->hasRole($roles))
    	{
    		/* TODO : go eat burger at Chibby's Dinner - no tree construction, just linear linkage */
    		if (!$this->hasRole("distant"))
    			$this->addRole(new Zend_Acl_Role("distant"), self::GUEST);
    		$this->addRole($roles, "distant");
    	}
        return $this->setRule(self::OP_ADD, self::TYPE_DENY, $roles, $resources, $privileges, $assert);
    }
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $resource
	 * @param unknown_type $privilege
	 */
	public function ivcAllowed($resource = null, $privilege = null)
	{
	    //echo "IS ALLOWED [$this->_role] [$resource] [$privilege] : " . intval($this->isAllowed($this->_role, $resource, $privilege)) . "<br />";
		return ($this->isAllowed($this->_role, $resource, $privilege));
	}
	
/*	public function isAllowed($role = null, $resource = null, $privilege = null)
	{	        
        parent::isAllowed($this->_role, $resource, $privilege);
	}*/
	
	
	public function getParentRole($role)
	{
	    $roleList = $this->_roleRegistry->getRoles();
	    if (isset($roleList[$role]))
	    {
	        foreach ($roleList[$role]['parents'] as $key => $role)
	        {
	            return ($key);
	        }
	    }
	    return (null);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $curentResource
	 * @param unknown_type $role
	 */
	public function getResourcePriv($curentResource, $role)
	{
		foreach ($this->_resources as $resource => $ref)
		{
			if ($resource == $curentResource)
			{
				//echo "RESOURCE $resource<br />";
				if (isset($ref['parent']))
					$privList = $this->getResourcePriv($ref['parent']->getResourceId(), $role);
				else
					$privList = array();
				foreach ($this->_rules['byResourceId'] as $resourceRule => $refRule)
				{
					if ($resourceRule == $curentResource)
					{
						//echo "Get $resourceRule Rules<br />";
						foreach ($refRule['byRoleId'] as $roleRule => $refRole)
						{
							//echo "Role $roleRule<br />";
							if ($roleRule == $role)
							{	
								foreach ($refRole['byPrivilegeId'] as $privRule => $refPriv)
								{
									//echo "Priv $privRule : " . $refPriv['type'] . "<br />";
									$privList[$privRule] = $refPriv['type'];
								}
							}
						}
					}
				}
				return ($privList);
			}
		}
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $curentRole
	 * @param unknown_type $spaceCount
	 */
	public function getRules() {
	    return $this->_rules;
	}
	
	public function printRoleTree($curentRole, $spaceCount)
	{
		$privilegeList = array();
		$roleList = $this->_roleRegistry->getRoles();
		foreach ($this->_rules['allResources']['byRoleId'] as $key => $value)
		{
			$roleList[] = $key;
			foreach ($value['byPrivilegeId'] as $privilege => $permition)
			{
				if (!in_array($privilege, $privilegeList))
				{
					$privilegeList[] = $privilege;
				}
			}
		}
		$count = 0;
		while ($count < $spaceCount)
		{
			echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp;";
			$count++;
		}
		foreach ($roleList as $role => $ref)
		{
			if ($role === $curentRole)
			{
				echo "$role [" . count($ref["parents"]) . "|" . count($ref["children"]) . "] Privs: ";
				foreach($privilegeList as $privilege)
				{
					echo $this->isAllowed($role, null, $privilege) ? "<font color=\"green\">$privilege</font> " : "<font color=\"red\">$privilege</font> ";
				}
				echo "<br />";
				$mdrflag = 0;
				foreach ($this->getResources() as $resource)
				{
					$rules = $this->getResourcePriv($resource, $role);
					if (count($rules)) 
					{
						
						if (!$mdrflag)
						{
							$count = 0;
							while ($count < $spaceCount + 1)
							{
								echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp;";
								$count++;
							}
							$mdrflag++;
						}
						
						echo "<font color=\"blue\">$resource </font>";
						//foreach ($rules as $rName => $type)
						//{
						//	echo $type == "TYPE_ALLOW" ? "<font color=\"green\">$rName</font> " : "<font color=\"red\">$rName</font> ";
						//}
						//echo " isAllowed: ";
						foreach ($rules as $rName => $type)
						{
							echo $this->isAllowed($role, $resource, $rName) ? "<font color=\"green\">$rName</font> " : "<font color=\"red\">$rName</font> ";
						}
						
					}
				}
				if ($mdrflag)echo "<br />";
				foreach ($ref["children"] as $childrenRole => $childrenRef)
				{
					$this->printRoleTree($childrenRole, $spaceCount + 1);
				}
			}
		}
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $resource
	 * @param unknown_type $privilege
	 */
	public function printIsAllowed($resource = NULL, $privilege = NULL)
	{
		if ($privilege == NULL)
			$strPrivilege = "all";
		else
			$strPrivilege = $privilege;
		echo $this->ivcAllowed($resource, $privilege) ? "<font color=\"green\">$resource:$strPrivilege</font> " : "<font color=\"red\">$resource:$strPrivilege</font> ";
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $role
	 * @param unknown_type $resource
	 * @param unknown_type $privilege
	 */
	public function printRoleIsAllowed($role, $resource = NULL, $privilege = NULL)
	{
		if ($privilege == NULL)
			$strPrivilege = "all";
		else
			$strPrivilege = $privilege;
		echo $this->isAllowed($role, $resource, $privilege) ? "<font color=\"green\">$resource:$strPrivilege</font> " : "<font color=\"red\">$resource:$strPrivilege</font> ";
	}
}