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
 *
 * @author		Jonathan Hickson
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Ivc_Core
 */

class Ivc_Core implements Zend_Acl_Resource_Interface
{
	private $_acl = null;
	private $_message = null;
	private	$_userGateway = null;
	private	$_clubGateway = null;
	
	private	$_clubId = null;
	private	$_clubObj = null;
	
	private $_userId = null;
	private $_userObj = null;
	
	private $_memberObj = null;
	
	private $_curDate = null; // Date of the day
	
	private $_treasury = null;
	private $_portfolio = null;
	
	private $_cache = null;
	
	public function getResourceId() {
	}
	
	protected function init($data)
	{
		if (isset($data['clubId']))
			$this->setClubId($data['clubId']);
		
		if ($this->getClubId() && null == $this->getClub())
			throw new Ivc_Exception(Ivc_Message::ERROR, 'Invalid Club Id');
		$this->setAclRules();
	}
	
	protected function getAcl()
	{
		if (!$this->_acl)
			$this->_acl = Zend_Registry::get('Ivc_Acl');
		return ($this->_acl);
	}
	
	public function checkAcl($action)
	{
		return $this->getAcl()->isAllowed(Ivc::getCurrentUser(), $this, $action);
	}
	
	protected function checkAcls($actions)
	{
		$out = array();
		foreach($actions as $action) {
			if ($this->getAcl()->ivcAllowed($this, $action))
				$out[] = $action;
		}
		return ($out);
	}
	
	public function getMessageInstance()
    {
        if (null === $this->_message) {
            $this->_message = Ivc_Message::getInstance();            
        }
        return $this->_message;
    }
    
    public function getMessages()
    {
    	return $this->getMessageInstance()->toArray();
    }
	
    public function getMember()
    {
    	if ($this->_memberObj == null) {
    		if (Zend_Registry::isRegistered('coreMemberObj' . $this->getUserId()) == false) {
    			$this->_memberObj = $this->getClubGateway()->fetchMember($this->getUser(), 'active');
    			Zend_Registry::set('coreMemberObj' . $this->getUserId(), $this->_memberObj);
    		}
    		else
    			$this->_memberObj = Zend_Registry::get('coreMemberObj' . $this->getUserId());
    	}
    	return ($this->_memberObj);
    }
    
	public function getClubGateway()
	{
		if (!$this->_clubGateway)
			$this->_clubGateway = new Ivc_Model_Clubs_Gateway();
		return($this->_clubGateway);
	}
	
	public function getClubId()
	{
		if (!$this->_clubId)
			if ($this->getUser()->hasClub())
				$this->_clubId = $this->getMember()->getClub()->club_id;
		return ($this->_clubId);
	}
	
	protected function setClubId($clubId)
	{
		$this->_clubId = $clubId;
		$this->_clubGateway = null;
		$this->_clubObj = null;
	}
	
	public function getClub()
	{
		if ($this->getUserId() !== 'guest' && !$this->_clubObj) {
			if (Zend_Registry::isRegistered('coreClubObj' . $this->getClubId()) == false) {
				$this->_clubObj = $this->getClubGateway()->fetchClubById($this->getClubId());
				Zend_Registry::set('coreClubObj' . $this->getClubId(), $this->_clubObj);
			}
			else
				$this->_clubObj = Zend_Registry::get('coreClubObj' . $this->getClubId());
		}
		return $this->_clubObj;
	}
	
	/**
	 * @return Ivc_Model_Users_Gateway
	 */
	public function getUserGateway()
	{
		if (!$this->_userGateway)
			$this->_userGateway = new Ivc_Model_Users_Gateway();
		return ($this->_userGateway);
	}
	
	public function getUserId()
	{
		if (!$this->_userId)
			$this->_userId = Ivc::getCurrentUserId();
		return ($this->_userId);
	}
	
	protected function setUserId($userId)
	{
		$this->_userId = $userId;
		$this->_userObj = null;
		$this->_memberObj = null;
		$this->_clubId = null;
		$this->_clubObj = null;
	}
	
	public function getUser()
	{
		if (!$this->_userObj)
			if ($this->getUserId() == 'guest')
				$this->_userObj = Ivc::getCurrentUser();
			else {
				if (Zend_Registry::isRegistered('coreUserObj' . $this->getUserId()) == false) {
					$this->_userObj = $this->getUserGateway()->fetchUser($this->getUserId());
					Zend_Registry::set('coreUserObj' . $this->getUserId(), $this->_userObj);
				}
				else
					$this->_userObj = Zend_Registry::get('coreUserObj' . $this->getUserId());
			}
		return $this->_userObj;
	}
	
	public function setDate($date)
	{
		if ($date)
			$this->_curDate = $date;
		//if (self::$_treasury != null)
		//	self::$_treasury->setDate($date);
	}
	
	public function getDate()
	{
		if (!$this->_curDate)
			$this->_curDate = date("Y-m-d");
		return ($this->_curDate);
	}
	
	function getTreasury()
	{
		$cs = Zend_Registry::get('Construct_stats');
		$cs['core_treasury'] += 1;
		Zend_Registry::set('Construct_stats', $cs);
		if ($this->_treasury === null) {
			if (Zend_Registry::isRegistered('coreTreasury' . $this->getClubId()) == false) {
				$this->_treasury = new Model_Treasury_Treasury(array('clubId' => $this->getClubId(), 'date' => $this->getDate()));
				Zend_Registry::set('coreTreasury' . $this->getClubId(), $this->_treasury);
			}
			else
				$this->_treasury = Zend_Registry::get('coreTreasury' . $this->getClubId());
		}
		return ($this->_treasury);
	}
	
	public function getPortfolio()
	{
		$cs = Zend_Registry::get('Construct_stats');
		$cs['core_portfolio'] += 1;
		Zend_Registry::set('Construct_stats', $cs);
		if (!$this->_portfolio) {
			if ($this->_portfolio === null) {
				if (Zend_Registry::isRegistered('corePortfolio' . $this->getClubId()) == false) {
					$this->_portfolio = new Model_Portfolio_Portfolio(array('clubId' => $this->getClubId()/*,
					'treasuryRef' => $this->getTreasury()*/));
					Zend_Registry::set('corePortfolio' . $this->getClubId(), $this->_portfolio);
				}
				else
					$this->_portfolio = Zend_Registry::get('corePortfolio' . $this->getClubId());
			}
		}
		return ($this->_portfolio);
	}
	
	public function getCache()
	{
		if (!$this->_cache)
			$this->_cache = Ivc_Cache::getInstance();
		return ($this->_cache);
	}
	
}