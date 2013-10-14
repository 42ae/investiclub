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
 * @subpackage	Contribution
 */

/* TODO : add fees for late contributions */

class Model_Treasury_Contribution extends Ivc_Core
{
    private $_mapper = null;
    private $_contributionList = null;
    private $_treasuryName = null;
    
    public function __construct(array $options = null)
    {
		if (is_array($options))
			$this->setOptions($options);
			
		$this->setAclRules();
    }
    
	public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }
    
	public function setTreasuryName($name)
    {
    	$this->_treasuryName = $name;
    	$this->getTreasury()->setTreasuryName($this->_treasuryName);
    	return ($this);
    }
    
    public function getResourceId()
    {
        return ('club:' . $this->getClubId() . ':contribution');
    }
    
    private function setAclRules()
    {
        $acl = Zend_Registry::get('Ivc_Acl');
        if ($acl->has($this->getResourceId())) // if already set return
            return;
        $acl->add(new Zend_Acl_Resource($this->getResourceId()));

        // Set Guest and User rules
        $acl->deny(Ivc_Acl::GUEST, $this, null);
        $acl->deny(Ivc_Acl::USER, $this, null);
        // Set club default rules
        if ($this->getClubId())
        {
			$acl//->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, null)
			    ->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'listContrib')
			    ->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'addContrib')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'delContrib')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'editContrib')
            	->allow(Ivc_Acl::CLUB_ADMIN . $this->getClubId(), $this, null);
        }
        // Set dynamic rules, works for external users rights
        Ivc_Acl_Factory::setDynAcl($acl, $this);
        return ($this);
    }
    
    /*public function getTreasury()
    {
        if (!$this->_treasury)
            $this->_treasury = new Model_Treasury_Treasury(array('clubId' => $this->getClubId(),
            													 'date' => $this->getDate(),
            													 'treasuryName' => $this->_treasuryName));
        return ($this->_treasury);
    }*/
    
    private function getMapper()
    {
        if (!$this->_mapper)
            $this->_mapper = new Model_Treasury_TreasuryMapper($this->getClubId());
        return ($this->_mapper);
    }
	
	public function getMessages()
	{
        return $this->getMessageInstance()->toArray();
	}
	
    private function getLastInsertedId()
	{
	    return ($this->_lastInsertedId);
	}
	
	private function getContributionsList()
	{
	    if (!$this->_contributionList)
	    {
			$treasuryDates = $this->getTreasury()->getTreasuryDates();
	    	$this->_contributionList = $this->getMapper()->fetchContributions($treasuryDates['endDate']);
	    }
	    return ($this->_contributionList);
	}
	
	/**
	 * 
	 * Check Contributions for a given period
	 * return true if all Contribution are valid
	 */
	public function checkContribution()
	{
		$contribs = $this->getContributions();
		foreach ($contribs as $contrib)
		{
			if ($contrib ['status'] === "unpaid")
				return (false);
		}
		return (true);
	}
	
	/**
	 * 
	 * Add Contribution
	 * @param array $data
	 * 'club_user_id'<br />
	 * 'date' : std db format<br />
	 * 'value' : Contribution value<br />
	 * ['force'] : flag to bypass Warnings<br />
	 */
	public function addContribution($inputData) {
		// check and send alerts or notifications
		$data = array ('date' => $inputData ['contribution_date'], 'club_user_id' => $inputData ['id'], 'value' => $inputData ['amount'], 'comment' => $inputData['comment']);

	    $this->getTreasury()->addContribution($data);
		if ($this->getMessageInstance()->getLevel() < 2)
            {
               $this->getMessageInstance()->flush(); // clean previous warnings
               $this->getMessageInstance()->push(Ivc_Message::SUCCESS, Model_Treasury_Treasury::CONTRIBUTIONS_ADDED);
            }
	}
	
	/**
	 * 
	 * Add Contribution
	 * @param array $data
	 */
	public function addContributionList($inputData)
	{
		$members = array();
		foreach ($inputData as $key => $val)
		{
			if (!strncmp('contribution_member_', $key, 20))
				$members[intval(substr($key, 20))] = $val;
			else if ($key === 'contribution_date')
				$date = $val;
			else if ($key === 'comment')
				$comment = $val;
		}
		// check and send alerts or notifications ?
		$exFlag = 0;
		foreach ($members as $key => $val)
		{
			$data = array('date' => $date, 'club_user_id' => $key, 'value' => $val, 'comment' => $comment);
			$this->getTreasury()->addContribution($data);
			$exFlag = 1;
		}
		if (!$exFlag)
			throw new Ivc_Exception(Model_Treasury_Treasury::ERROR_OCCURED, Zend_Log::WARN);
		if ($this->getMessageInstance()->getLevel() < 2)
            {
               $this->getMessageInstance()->flush(); // clean previous warnings
               $this->getMessageInstance()->push(Ivc_Message::SUCCESS, Model_Treasury_Treasury::CONTRIBUTIONS_ADDED);
            }
		return ($this);
	}
	
	/**
	 * 
	 * Del Contribution
	 * @param array $data
	 * 'memberId' : contribution_id
	 */
	public function delContribution($memberId, $startDate, $endDate)
	{
	    // check and send alerts or notifications ?
	    $id = $this->getMapper()->findContributionId($memberId, $startDate, $endDate);
	    if (!$id)
	    	throw new Ivc_Exception(Model_Treasury_Treasury::NO_SUCH_ID, Zend_Log::WARN);
		$data = array('id' => $id);
	    $this->getTreasury()->delContribution($data);
	}
	
	/**
	 * 
	 * Edit Contribution
	 * @param array $data
	 * 'club_user_id'<br />
	 * 'date'<br />
	 * 'value'<br />
	 * 'sendNotif' : could do that !<br />
	 * ['force'] : flag to bypass Warnings<br />
	 */
	public function editContribution($data)
	{
	    $this->getMessageInstance()->push(Ivc_Message::WARNING, Model_Treasury_Treasury::NOT_IMPLEMENTED);
	    // check and send alerts or notifications
	    // call treasury editContribution()
	}
	
	
	public function sendReminder($data)
	{
		//$this->getMessageInstance()->push(Ivc_Message::WARNING, Model_Treasury_Treasury::NOT_IMPLEMENTED);
		$this->getMessageInstance()->push(Ivc_Message::SUCCESS, "Le rappel a été envoyé avec succès.");
	}
	
	/*
	 * Db Operation
	 * 
	 * Add 'contribution' to type enum into cashflow
	 * Table :Contributions
	 * contribution_id
	 * club_user_id
	 * club_id ?
	 * date
	 * value
	 * type (cheque, virement, ...)
	 * other infos
	 * 
	 */
	
	private function contributionSum($userContribution)
	{
	    $sum = 0;
	    foreach ($userContribution as $date => $val)
	        $sum += $val;
	    return ($sum);
	}
	
    public function getFirstDay($date) /* TODO : store into db period start (balancesheet)*/
    {
        preg_match('/(\d{4})-(\d{2})-(\d+)/', $date, $matches);
        return date('Y-m-d', mktime(0, 0, 0, $matches[2], 1, $matches[1]));
    }
    
    public function getLastDay($date)
    {
        preg_match('/(\d{4})-(\d{2})-(\d+)/', $date, $matches);
        $result = strtotime("{$matches[1]}-{$matches[2]}-01");
        $result = strtotime('-1 second', strtotime('+1 month', $result));
        return date('Y-m-d', $result);
    }
	
    private function getContributionStatus($club_user_id, $userContribution, $info)
    {
    	if (!$userContribution) // no contribution
    		return ("unpaid");
        
    	end($userContribution);
        $lastContrib = key($userContribution);
        $treasuryDates = $this->getTreasury()->getTreasuryDates();
        if ($lastContrib >= $treasuryDates['startDate'] && $lastContrib <= $treasuryDates['endDate'])
            return ("paid");
        //else if ($this->_curDate > $info['contribution_date'])
        //    return ("late");
        else
            return ("unpaid");
    }
    
    private function setContribAction()
    {
    	$dates = $this->getTreasury()->getTreasuryDates();
    	
    	if ($dates['status'] != 'closed')
    		return ($this->checkAcls(array('addContrib', 'delContrib', 'editContrib', 'listContrib')));
    	else
    		return ($this->checkAcls(array('listContrib')));
    }
    
	/**
	 * 
	 * Get Contributions
	 * @param array $data
	 * 'date' : date of the period, std db format<br />
	 */
	public function getContributions($data = null) /* TODO : Cache this one ! */
	{
		if (!$this->getAcl()->ivcAllowed($this, 'listContrib'))
            throw new Ivc_Acl_Exception;

		if ($data)
		{
			if (isset($data['date']))
				$this->setDate($data['date']);
			if (isset($data['treasuryName']))
				$this->setTreasuryName($data['treasuryName']);
		}
	    $treasury = $this->getTreasury();
	    $bilanList = $treasury->getTreasuryList();
        $gateway = new Ivc_Model_Clubs_Gateway();
        $club = $gateway->fetchClub($this->getClubId());
        $members = $gateway->fetchActiveMembersByDate($club, $this->_treasuryName);
        $contribution_date = new Zend_Date();
        $contribution_date->setDay(15); /* TODO : calculate this from period time and user pref */
            
        $memberInfo = array();
        foreach ($members as $id => $member)        
            $memberInfo[$id] = array(
            						"enrollement_date" => $member->enrollement_date,
                                  	"contribution_date" => $contribution_date->get("Y-MM-dd")
                                    );
	    $contribs = $this->getContributionsList();
	    $contribution = array();

	   	//Zend_Debug::dump($members);
	    foreach ($members as $member_id => $val)
	    {
	    	$totalPaid = 0;
	    	$lastContribution = null;
	    	$value = 185;  /* TODO : get club default value */
	    	if (!isset($contribs[$member_id])) // Never contribued
				$contribs[$member_id] = null;
			else
			{
				$totalPaid = $this->contributionSum($contribs[$member_id]);
				$value = end($contribs[$member_id]);
	        	$lastContribution = key($contribs[$member_id]);
			}
	        $status = $this->getContributionStatus($member_id, $contribs[$member_id], $memberInfo[$member_id]);
	         

	        $contribution[$member_id] = array('paid' => $totalPaid,
	                                             'value' => $value,
	                                             'lastContribution' => $lastContribution,
	                                             'status' => $status,
	        									 'action' => $this->setContribAction());
	    }
	    return ($contribution);
	}
	
}
