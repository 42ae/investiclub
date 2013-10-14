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
 * The Treasury is the class that handle the club contability. It store every operation made by the club
 * like buy a stock, add a cotisation or calcul a member departure share ...
 * 
 * The treasury contain two set of functions :
 * - Functions that calcul the treasury with active / passive (all function call : "funct_")
 * - Functions to add / remove or edit data
 * 
 * The treasury also save a balance sheet every month. The treasury use Ivc_Cache to store every month
 * that way it don't have to query the database and calculate everything all the time.
 * 
 * @author		Jonathan Hickson
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Treasury
 */

class Model_Treasury_Treasury extends Ivc_Core
{
    private $infos;
    private $_creationDate;                 // Club creation date
    private $_curTreasuryName = null;				// Date of treasury
    
    private $_mapper = null;
    private $_bilanList;                    // List of Bilan ($_bilanList['date'] = data)
    private $_bilanDatesList = null;        // List all available Bilan ($_bilanDatesList['date'] = id)
	private $_bilanPendingList = null;
	private $_bilanClosedList = null;
    private $_debug = false;
    private $_portfolio = null;
    private $_lastInsertedId = null;               // last insert id used for tables dependancy
    
    private	$curentMonth; // Used by calculateAll
    private	$_members; // temporary, for friendly display
	
    const BAD_PARAM              = 'Paramètre inccorect.';
    const BUY_ADDED              = 'Action ajoutée avec succès.';
    const BUY_EDITED             = 'Action éditée avec succès.';
    const BUY_DELETED            = 'Action supprimée avec succès.';
    const CREDIT_ADDED           = 'Ajout de crédit effectué.';
    const CREDIT_DELETED         = 'Ajout de crédit supprimé.';
    const CONTRIBUTION_ADDED     = 'La contribution a été ajoutée avec succès.';
    const CONTRIBUTION_EDITED    = 'La contribution a été supprimée avec succès.';
    const CONTRIBUTIONS_ADDED    = 'Les contributions ont été ajoutées avec succès.';
    const CONTRIBUTIONS_DELETED  = 'Les contributions ont été supprimées avec succès.';
    const DEBIT_ADDED            = 'Le débit à été effectué avec succès.';
    const DEBIT_DELETED          = 'Le débit à été supprimé avec succès.';
    const DIVIDEND_ADDED         = 'Les dividendes ont été ajoutés avec succès.';
    const NOT_ENOUGH_CASH        = 'Vous n\'avez pas assez d\'argent pour effecter cette opération.';
    const PROFIT_DISTRIBUTED     = 'Les profits ont été redistribués.';
    const SELL_ADDED             = 'Action vendue avec succès.' ;
    const SELL_DELETED           = 'Action editée avec succès.';
    const SELL_EDITED			 = 'Action supprimée avec succès.';
    const TREASURY_DOES_NOT_EXIST = 'Action impossible. La tresoririe n\'existe pas.'; // date in the future
    const TREASURY_CLOSED        = 'Action impossible. La période est cloturée.'; // date in the past ???????????????
    const ERROR_OCCURED          = 'Une erreur s\'est produite, l\'administrateur a été alerté.'; // log
    const NO_ID_SPECIFIED        = 'Une erreur s\'est produite, l\'administrateur a été alerté.'; // log
    const NO_SUCH_ID             = 'Une erreur s\'est produite, l\'administrateur a été alerté.'; // log
    const NOT_CURRENT_CLUB_ID    = 'Une erreur s\'est produite, l\'administrateur a été alerté.'; // log
    const NOT_IMPLEMENTED		 = 'Une erreur s\'est produite, l\'administrateur a été alerté.'; // log
    const NOT_A_VALIDATION_STATUS = 'notAValidtionStatus';
    const PREV_PERRIOD_NOT_CLOSED = 'prevPeriodNotClosed';
    const VALIDATE_CONTRIB_FIRST = 'validateContribFrist';
    const PERIOD_CLOSED			 = 'periodClosed';
	
    /*
    protected $_messageTemplates = array(
        self::INVALID           => "An error occured !",
        self::OKGOOD            => "OK GOOD, what else ?",
        self::NOTENOUGHMONEY    => "Not Enough Money",
    );
    */
    
    public function __construct(array $options = null)
	{		
		//$this->member = Ivc::getCurrentMember();
		//if (Ivc::getCurrentUser()->hasClub())
			//$this->getClubId() = Ivc::getCurrentMember()->getClub()->club_id;
			
				/*
			$gateway = new Ivc_Model_Clubs_Gateway();
            $this->members = $gateway->fetchMembers($this->getClubId(), array('active', 'pending'));
            
            foreach ($this->members as $k => $member) {
            	echo $k . ' '; 
            }
			*/
		
		$cs = Zend_Registry::get('Construct_stats');
		$cs['treasury'] += 1;
		Zend_Registry::set('Construct_stats', $cs);
		
		if (is_array($options))
			$this->setOptions($options);

        $this->setAclRules();

        if ($this->getUser()->hasClub())
        	$this->_creationDate = $this->getClub()->registration_date;
        
		$this->_bilanList = array();
		$this->infos = array('frais_depart' => 2); /* TODO : get infos properly */
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
    
    
    
    public function setTreasuryName($name = NULL)
    {
    	if ($name)
    		$this->_curTreasuryName = $this->getLastDay($name);
    	return ($this);
    }
    
    public function setClubCreationDate($cDate)
	{
		$this->_creationDate = $cDate;
	}
	
	public function setDebugMode($flag)
	{
		$this->_debug = $flag;
	}
    
    public function getResourceId()
    {
        return ('club:' . $this->getClubId() . ':treasury');
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
            $acl->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'viewTreasury')
            	->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, null) // DEV
            	->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'list')
            	->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'validatePeriod')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'addSell') // use the portfolio one ?
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'delSell')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'editSell')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'addBuy')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'delBuy')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'editBuy')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'addCredit')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'delCredit')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'editCredit')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'addDebit')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'delDebit')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'editDebit')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'addReevaluation')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'validatePeriod'); // getTreasuryPendingValidationList()
        }
        // Set dynamic rules, works for external users rights
        Ivc_Acl_Factory::setDynAcl($acl, $this);
    }
    
    private function getMapper()
    {
        if (!$this->_mapper)
            $this->_mapper = new Model_Treasury_TreasuryMapper($this->getClubId());
        return ($this->_mapper);
    }
    
    private function getMembers($date = null)
    {
        if ($date == null)
        	$date = $this->getDate();

        if (!isset($this->_members[$date]))
        {
            $gateway = new Ivc_Model_Clubs_Gateway();
            $club = $gateway->fetchClub($this->getClubId());
            $this->_members[$date] = $gateway->fetchActiveMembersByDate($club, $date);
        }
        return ($this->_members[$date]);
    }
    
    private function getMemberId($club_user_id, $date = null)
    {
    	$members = $this->getMembers($date);
    	foreach ($members as $id => $val)
    	{
    		if ($id == $club_user_id)
    			return ($val);
    	}
    	return (null);
    }
    
    /*
	public function getPortfolio()
	{
	    if (!$this->_portfolio)
	        $this->_portfolio = new Model_Portfolio_Portfolio(array('clubId' => $this->getClubId(),
	        														'treasuryRef' => $this));
	    return ($this->_portfolio);
	}
	*/
	/*
    public function getMessageInstance()
	{
	    if (!$this->_error)
	        $this->_error = Ivc_Message::getInstance(Ivc_Message::TR_PF);
	    return ($this->_error);
	}
	*/
    private function getLastInsertedId()
	{
	    return ($this->_lastInsertedId);
	}
    
	/**
	 * Print events list.
	 */
    public function	printEvents($events)
	{		
		foreach ($events as $key => $val)
		{
			echo "$key " . strtoupper($val['ope']) . " ";
			if ($val['ope'] === "cotisation")
				echo "membre=" . $this->getMemberId($val['club_user_id'])->first_name . " value=" . $val['value'];
			else if ($val['ope'] === "buy" OR $val['ope'] === "sell" OR $val['ope'] === "dividend")
				echo "share=" . $val['share'] . " value=" . $val['value'] . " ac_nb=" . $val['nb'] . " (somme=" . ($val['value'] * $val['nb']) . ")";
			else if ($val['ope'] === "debit")
				echo "value=" . $val['value'] . " comment=" . $val['comment'];
			else if ($val['ope'] === "demission")
				echo "member=" . $val['member'];
			echo "<br />";
		}
	}
	
	/**
	 * Function called to calculate member deposit.
	 */
	private function func_credit(&$data, $val)
	{ 
		$data['solde'] += $val['value'];
		if (!isset($data['cotisation_membre'][$val['club_user_id']]))
		{
			$data['cotisation_membre'][$val['club_user_id']]['value'] = 0;
			$data['cotisation_membre'][$val['club_user_id']]['paid'] = 0;
		}
		$data['cotisation_membre'][$val['club_user_id']]['value'] += $val['value'];
		$data['cotisation_membre'][$val['club_user_id']]['paid'] += $val['value'];
		if ($this->_debug)
		{
			echo 'Add cotisation from ' . $val['club_user_id'] . ' : ' . $val['value'] . '<br/>';
		}
	
		if ($data['unit'])
		    $data['unit_nb'] += $val['value'] / $data['unit'];
	}
	
	/**
	 * Function called to calculate stock buy.
	 */
	private function func_buy(&$data, $val)
	{
	    //$shares = $this->getPortfolio()->getPortfolioShares();
	    //$portfolio = $shares[$val['share_id']]->calculateToId($val['id']);
	    
		$data['solde'] -= (($val['price'] * $val['shares']) + $val['fees']);
		$data['pf'] += ($val['price'] * $val['shares'] + $val['fees']);
		if ($this->_debug)
			echo 'buy de ' . $val['shares'] . ' actions de ' . $val['symbol'] . '<br />'; 
	}

	/**
	 * Function called to calculate stock sell.
	 */
	private function func_sell(&$data, &$val)
	{
	    $shares = $this->getPortfolio()->getPortfolioStocks();
	    $portfolio = $shares[$val['stock_id']]->calculateToId($val['id']);
	    $val['value'] = ($portfolio['revient'] + $portfolio['profit']) * $val['shares'];
		$data['solde'] += ($portfolio['revient'] + $portfolio['profit']) * $val['shares'];
		$data['pf'] -= ($portfolio['revient']) * $val['shares'];
		$data['profit'] += $portfolio['profit'] * $val['shares'];
		$val['profit'] = $portfolio['profit'] * $val['shares']; // For Display
		if ($this->_debug)
			echo 'sell de ' . $val['shares'] . ' actions de ' . $val['symbol'] . ' benef:' . ($portfolio['profit'] * $val['shares']) . '<br />';
	    
		$part = $this->calcMemberPart($data);
	     
	    foreach ($val['users'] as $user => $action) /* TODO : just add entry when there is a debit */
	    {
            if (isset($part[$user])) {
                $value = $part[$user] * $portfolio['profit'] * $val['shares'];
                $data['cotisation_membre'][$user]['value'] += $value;
                $tmp = array('value' => $value, 'club_user_id' => $user, 
                'comment' => "");
                if ($action === 'debit')
                    $this->func_debit($data, $tmp);
                elseif ($action === 'credit') // for credit nothing to do because money is already on the member share
				{
                    if ($this->_debug)
                    {
                        echo 'Virtual Credit from ' .
                         $this->getMemberId($tmp['club_user_id'])->first_name . ' of ' .
                         $tmp['value'] . ' ' . $tmp['comment'] . '<br />';
                    }
                }
                unset($tmp);
            }
        }
       
	}
	
	/**
	 * Function called to calculate a cash withdraw.
	 */
	private function func_debit(&$data, $val)
	{
		$data['solde'] -= $val['value'];
		if ($val['club_user_id'])
		{
		    $data['cotisation_membre'][$val['club_user_id']]['value'] -= $val['value'];
		    if ($this->_debug)
		    {
			    echo 'Debit from ' . $this->getMemberId($val['club_user_id'])->first_name 
			        . ' of ' . $val['value'] . ' ' . $val['comment'] . '<br />';
		    }
			if ($data['unit'])
		        $data['unit_nb'] -= $val['value'] / $data['unit'];
		}
		else
		{
		    $part = $this->calcMemberPart($data);
		    foreach ($data['cotisation_membre'] as $membre => $info)
	        $data['cotisation_membre'][$membre] += $part[$membre] * $val['value'];
		}
	}
	
	/**
	 * Function that calculate a member share.
	 */
	private function calcMemberPart($data)
	{
	    $capital_club = 0;
		foreach ($data['cotisation_membre'] as $membre => $info)
			$capital_club += $info['value'];
		
		$part = array();
	    foreach ($data['cotisation_membre'] as $user => $info)
	        $part[$user] = $info['value'] / $capital_club;
	    return ($part);
	}
	
	/**
	 * Function called to calculate a "dividend".
	 */
	private function func_dividend(&$data, $val)
	{
		$data['solde'] += $val['price'] * $val['shares'];
        $data['profit'] += $val['price'] * $val['shares'];
		if ($this->_debug)
			echo 'Add dividends of ' . $val['symbol'] . ' ne sont pas compte<br />';

		$part = $this->calcMemberPart($data);
	    foreach ($val['users'] as $user => $action)
	    {
	        $value = $part[$user] * $val['price'] * $val['shares'];
	        $data['cotisation_membre'][$user]['value'] += $value;
	        $tmp = array('value' => $value, 'club_user_id' => $user, 'comment' => " dividend from " . $val['symbol']);
	        
	        if ($action === 'debit') // for credit nothing to do because money is already on the member share
	            $this->func_debit($data, $tmp);
	        elseif($action === 'credit')
	        {
	           if ($this->_debug)
	           {
			        echo 'Virtual Credit from ' . $tmp['club_user_id'] 
			        . ' of ' . $tmp['value'] . ' ' . $tmp['comment'] . '<br />';
	           }
	        }
	        unset($tmp);
	    }
	}
	
	/**
	 * Function called to reevaluate portfolio's value
	 */
	private function func_reevaluation(&$data, &$val)
	{
	    $capital_club = 0;
		foreach ($data['cotisation_membre'] as $membre => $info)
			$capital_club += $info['value'];
			
		$val['old_pf'] = $data['pf'];
		$val['old_capital'] = $capital_club;
	    if ($this->_debug)
	    {
			echo 'Reevaluation of the portfolio of ' . $val['value'] . '<br />';
			echo 'ACTIF:('. ($data['pf'] + $data['solde']) .') PF = ' . $data['pf'] 
				. ', Caisse = ' . ($data['solde']) . ')'
				. "<br />PASSIF:($capital_club) Capital = $capital_club" 
				. ", Resultat exercice = XXX"  . '<br />';
			echo 'Unit: ' . $data['unit'] . ' unitNb: ' . $data['unit_nb'] . '<br />';
	    }
	    $data['pf'] += $val['value'];
	    
	    $part = $this->calcMemberPart($data);
	    foreach ($data['cotisation_membre'] as $membre => $info)
	        $data['cotisation_membre'][$membre]['value'] += $part[$membre] * $val['value'];
	    
	    $capital_club = 0;
		foreach ($data['cotisation_membre'] as $membre => $info)
		    $capital_club += $info['value'];
		    
		if ($data['unit_nb'] == 0)
	        $data['unit_nb'] = $capital_club;
	    if ($data['unit_nb'])
	        $data['unit'] = $capital_club / $data['unit_nb'];
		    
	    $val['new_pf'] = $data['pf'];
	    $val['new_capital'] = $capital_club;
		if ($this->_debug)
		{    
		    echo 'ACTIF:('. ($data['pf'] + $data['solde']) .') PF = ' . $data['pf'] 
			    . ', Caisse = ' . ($data['solde']) . ')'
				. "<br />PASSIF:($capital_club) Capital = $capital_club"
				. ", Resultat exercice = XXX" . '<br />';
		    echo 'Unit: ' . $data['unit'] . ' unitNb: ' . $data['unit_nb'] . '<br />';
		} 
	}
	
	/**
	 * Function called to calculate a member departure share.
	 */
	private function func_demission(&$data, &$val)
	{
        // Get member share and calc fees
        if (isset($data['cotisation_membre'][$val['club_user_id']]) == false)
        	$data['cotisation_membre'][$val['club_user_id']]['value'] = 0;
		$part = $data['cotisation_membre'][$val['club_user_id']]['value'];
		$part_frais = $part - $part * $data['info']['frais_depart'] / 100;
        $frais_statutaires = $part - $part_frais;
        
		$data['solde'] -= $part_frais;
        $val['value'] = $part_frais; // For Display
        $val['fees'] = $frais_statutaires; // For Display
		if ($data['unit'])
		        $data['unit_nb'] -= $part_frais / $data['unit'];
		
		// delete member from array 
		unset($data['cotisation_membre'][$val['club_user_id']]);
		//unset($this->_members[$val['club_user_id']]);
		// Distrib of fees
		$part = $this->calcMemberPart($data);
	    foreach ($data['cotisation_membre'] as $membre => $info)
	        $data['cotisation_membre'][$membre]['value'] += $part[$membre] * $frais_statutaires;
		
		if ($this->_debug)
		{
		    $capital_club = 0;
		    foreach ($data['cotisation_membre'] as $membre => $info)
			    $capital_club += $info['value'];
			echo "Depart of a member<br />Part of the departing member: $part ($part_frais)<br />";	
			echo "Debit de $part_frais<br />";
			echo 'ACTIF:('. ($data['pf'] + $data['solde']) .') PF = ' . $data['pf'] 
				. ', Caisse = ' . ($data['solde']) . ')'
				. "<br />PASSIF:(" . ($capital_club ) . ") Capital = " .($capital_club) 
				. ", Resultat exercice = XXX" . '<br />'; 
		}
	}
	
	/**
	 * Function called to add a new member to the treasury.
	 */
	private function func_nouveaumembre(&$data, $val)
	{
	    $data['cotisation_membre'][$val['club_user_id']] = array('value' => 0, 'paid' => 0);
	}
	
	/**
	 * Function called to run end of month assessment.
	 */
	private function func_fin_mois(&$data, $val)
	{
	    
		$capital_club = 0;
		foreach ($data['cotisation_membre'] as $membre => $cotisation)
			$capital_club += $cotisation;
			
	    if ($data['unit_nb'] == 0)
	        $data['unit_nb'] = $capital_club;
	    if ($data['unit_nb'])
	        $data['unit'] = $capital_club / $data['unit_nb'];
			
		if ($this->_debug)
		{
			echo '<H1>'
				. 'ACTIF:('. ($data['pf'] + $data['solde']) .') PF = ' . $data['pf'] 
				. ', Caisse = ' . $data['solde']  . ')'
				. "</H1><H1>PASSIF:( $capital_club ) Capital = " . ($capital_club - $data['profit']) 
				. ", Resultat exercice = " . $data['profit']
				. '</H1>';
			echo 'Unit: ' . $data['unit'] . ' unitNb: ' . $data['unit_nb'] . '<br />';
			echo 'Vars: $data[pf]=' . $data['pf']
				. ' $data[solde]=' . $data['solde']
				. ' $capital_club=' . $capital_club . "<br />";		
		}
	}
	
	
	
	/**
	 * Check if date in param is before the club creation. This function is used as a limit for assessment recursive generation.
	 */
	private function _isBeforeClubCreation($date)
	{
		$diff = strtotime($date) - strtotime($this->_creationDate);
		if ($diff <= 0)
			return (TRUE);
		return (FALSE);
	}
	
	/**
	 * Check if the date in parameter correspond to an over month
	 */
	private function _isOver($date)
	{
		$lastDay = $this->getLastDay($date);
		$diff = strtotime($this->getDate()) - strtotime($lastDay);
		if ($diff < 0)
			return (FALSE);
		return (TRUE);
	}
	
	/**
	 * Recursive methode that calculate month's assessment.
	 * It will try to calculate the assessment of the month in parameter from the previous assessment.
	 * If no previous assessment exist it will then try to calculate the previous one until the club creation date.
	 */
	private function calculateMonth($month) // calculate on month  ex: 2011-05-01
	{
		// check if previous month exist. if not calculate recursively
		$month = $this->getFirstDay($month);
		$prev = $this->getPrevMonth($month);
		$prevLast = $this->getLastDay($prev);
		$data = $this->getMapper()->fetchBilan($prevLast);
		if ((!$data || $data['status'] != 'closed')&& $this->_isBeforeClubCreation($month) === FALSE)
		{	
			if ($this->_debug)
				echo "No bilan for $prevLast, calculate it<br />";
			$data = $this->calculateMonth($prev);
			
		}
		if ($this->_isBeforeClubCreation($month))
		{
			if ($this->_debug)
				echo "First bilan<br />";
		    $data = array();
			$data['solde'] = 0.0;
			$data['pf'] = 0.0;
			$data['total_profit'] = 0.0;
			$data['unit'] = 1.0;
		    $data['unit_nb'] = 0.0;
			$data['cotisation_membre'] = array();
		}
		//else
		//{
		//	$data = $this->getMapper()->fetchBilan($prevLast);
		//}
		if ($data)
		{
		    $data['info'] = $this->infos; // Get club parameters
		    $data['profit'] = 0.0; // start from 0 to calcul new profits
			$data['perf'] = 0.0;
			$data['stats'] = array();
			$prevProfit = 0; // STATS
			$prevTotalProfit = $data['total_profit'];
            // get Events for current month
		    if ($this->_debug)
			    echo "initevents from $month to " . $this->getLastDay($month) . "<br />";
		    $data['events'] = array();
		    $this->getMapper()->initEvents($data['events'], $month, $this->getLastDay($month));
		    foreach ($data['events'] as $key => &$val)
			{
				$this->{'func_' . $val['ope']}($data, $val);
				$data['events'][$key]['solde'] = $data['solde'];
				if ($this->_debug)
					echo 'Caisse: ' . $data['solde'] . ', Pf: ' . $data['pf'] . ', Profits: ' . $data['profit'] . '<br />';
				// Start Stats
				if (isset($data['stats'][$this->getDateFromKey($key)]['profit']))
					$data['stats'][$this->getDateFromKey($key)]['profit'] += $data['profit'] - $prevProfit;
				else
					$data['stats'][$this->getDateFromKey($key)]['profit'] = $data['profit'] - $prevProfit;
				if ($data['profit'] != $prevProfit)
					$prevProfit = $data['profit'];	
				$data['stats'][$this->getDateFromKey($key)]['total_profit'] = $prevTotalProfit + $data['profit'];
				$capital_club = 0;
				foreach ($data['cotisation_membre'] as $membre => $info)
					$capital_club += $info['value'];
				$data['stats'][$this->getDateFromKey($key)]['capital_club'] = $capital_club;
				$data['stats'][$this->getDateFromKey($key)]['unit_nb'] = $capital_club;
				if ($data['unit_nb'])
					$data['stats'][$this->getDateFromKey($key)]['unit'] = $capital_club / $data['unit_nb'];
				else
					$data['stats'][$this->getDateFromKey($key)]['unit'];
				if ($capital_club)
					$data['stats'][$this->getDateFromKey($key)]['perf'] = $data['total_profit'] / $capital_club * 100;
				else
					$data['stats'][$this->getDateFromKey($key)]['perf'] = 0;
				// End Stats
			}
			$data['total_profit'] += $data['profit'];
			$capital_club = 0;
			$data['perf'] = 0;
			foreach ($data['cotisation_membre'] as $membre => $info)
			            $capital_club += $info['value'];
			if ($capital_club)
			{
			    $data['perf'] = $data['total_profit'] / $capital_club * 100;
			    if ($data['unit_nb'] == 0)
	                $data['unit_nb'] = $capital_club;
	            if ($data['unit_nb'])
	                $data['unit'] = $capital_club / $data['unit_nb'];
			}
			/* TODO : we can save some "fetchBilan" call here ...*/
			// check if we can save bilan (month ended or already saved)
			/* TODO : check cotisaion and check reevaluation */
			/* TODO : bug: status stick to ongoing when not requesting lqst month */
			$bilan = $this->getMapper()->fetchBilan($this->getLastDay($month));
			// Create the entry for the new month
			if (!$bilan) 
			{
			    $data['status'] = 'ongoing';
			    $backupCotisation = $data['cotisation_membre'];
			    unset($data['balance_sheet_id']);
			    unset($data['cotisation_membre']);
			    $this->getMapper()->saveBilan($data, $this->getLastDay($month));
			    $data['cotisation_membre'] = $backupCotisation;
			    $data['flag_revaluation'] = false; // Useful ?
        	    $data['flag_cotisation'] = false; // Useful ?
			}
			// If month is over set need validation status
		     else if ($bilan && $bilan['status'] === 'ongoing' && $this->_isOver($month))
			{
			    $data['status'] = 'validation';
			    $data['balance_sheet_id'] = $bilan['balance_sheet_id'];
			    $backupCotisation = $data['cotisation_membre'];
			    unset($data['cotisation_membre']);
			    $data['balance_sheet_id'] = $bilan['balance_sheet_id'];
			    $this->getMapper()->saveBilan($data, $this->getLastDay($month));
			    $data['cotisation_membre'] = $backupCotisation;
			    $data['flag_revaluation'] = false; // Useful ?
        	    $data['flag_cotisation'] = false; // Useful ?
			}
			// If month not over continue
			else if ($bilan && $bilan['status'] === 'ongoing')
			{
			    $data['status'] = 'ongoing';
			    $data['flag_revaluation'] = false; // Useful ?
        	    $data['flag_cotisation'] = false; // Useful ?
			}
		   // If month valided, close and write it to db
			else if ($bilan && $bilan['status'] === 'valid')
			{
			    $data['status'] = 'closed';
			    $data['balance_sheet_id'] = $bilan['balance_sheet_id'];
			    $this->getMapper()->saveBilan($data, $this->getLastDay($month));
			    
			    $data['flag_revaluation'] = true; // Useful ?
        	    $data['flag_cotisation'] = true; // Useful ?
			}
			else if ($bilan && $bilan['status'] === 'closed')
			{
			    $data['status'] = 'closed';
			    $data['flag_revaluation'] = true; // Useful ?
        	    $data['flag_cotisation'] = true; // Useful ?
			}
		    else if ($bilan && $bilan['status'] === 'validation')
			{
			    $data['status'] = 'validation';
			    $data['flag_revaluation'] = false; // Useful ?
        	    $data['flag_cotisation'] = true; // Useful ?
			}
			else
			    Zend_Debug::dump($data);
			/*
			if (!$this->getMapper()->fetchBilan($this->getLastDay($month)) && $this->_isOver($month))// no bilan for cur month and is over
			{
			    $this->func_fin_mois($data, NULL);
			    $data['status'] = 'closed';
			    $this->getMapper()->saveBilan($data, $this->getLastDay($month));
			    //$pdf = new Model_Document_PdfGenerator($data, $this->getClubId());
			    $data['status'] = 'closed'; \/* TODO : set and get status from DB *\/
        	}
        	elseif ($this->getMapper()->fetchBilan($this->getLastDay($month)))
        	{
        	    $data['status'] = 'closed';
        	    $data['flag_revaluation'] = true;
        	    $data['flag_cotisation'] = true;
        	}
        	else
        	{   
        	    $data['flag_revaluation'] = false; // Check it
        	    $data['flag_cotisation'] = true; // Check it
        	    $data['status'] = 'ongoing';
        	}
        	*/
        	$capital = 0;
		    foreach ($data['cotisation_membre'] as $membre => $info)
			    $capital += $info['value'];
			$data['capital'] = $capital;
			$data['memberStats'] = $this->calculateMemberStats($data);
        	return ($data);
		}
		//else
			//echo "ERROR : can't get bilan info<br />";
			return (NULL);
	}
	
	private function calculateMemberStats($data)
	{
	    $memberStats = array();
	    $memberShare = $this->calcMemberPart($data);
	    foreach ($data['cotisation_membre'] as $member => $info)
	    {
	        $memberStats[$member]['value'] = round($info['value'], 2);
	        $memberStats[$member]['paid'] = $info['paid'];
	        $memberStats[$member]['shareP'] = round($memberShare[$member] * 100, 2);
	        $memberStats[$member]['unitNb'] = round($data['unit_nb'] * $memberShare[$member], 2);
	        if ($info['paid'])
	        	$memberStats[$member]['perfP'] = round($info['value'] / $info['paid'] * 100, 2);
	        else
	        	$memberStats[$member]['perfP'] = 0;
	    }
	    return ($memberStats);
	}
	
	/**
	 * Check if it's assessment time from last event date.
	 */
    private function _isBilanTime($strDate, &$curentMonth)
	{
		if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $strDate, $matches))
			if ($matches[2] != $curentMonth)
			{
				$curentMonth = $matches[2];
				return (true);	
			}
		return (false);
	}
	
	/**
	 * Calculate the club assessment from its creation. Used for debuging because it output data without DB operation.
	 */
	public function calculateAll() // Calculate everything from the start
	{
	    $events = array();
	    $this->getMapper()->initEvents($events);
		$data = array();
		$data['info'] = $this->infos;
		$data['solde'] = 0;
		$data['pf'] = 0;
		$data['cotisation_membre'] = array();
		$data['profit'] = 0.0;
		$data['unit'] = 1;
		$data['unit_nb'] = 0;
		$totalProfit = 0;
		$curentMonth = "";
		foreach ($events as $key => $val)
		{
			if ($this->_isBilanTime($key, $curentMonth))
			{
				if ($this->_debug)
					echo "<H2>" . $this->getLastDay($this->getPrevMonth($this->getDateFromKey($key))) . "</H2>";
				$this->func_fin_mois($data, $val);
				
				if ($this->_debug)
				{
				    $totalProfit += $data['profit'];
				    $capital_club = 0;
		            foreach ($data['cotisation_membre'] as $membre => $info)
			            $capital_club += $info['value'];
				    echo 'Perf: ' . ($totalProfit / $capital_club * 100) . '%<br />';
				}
				$data['profit'] = 0.0;
			}
			echo $val['ope'] . "<br />";
			$this->{'func_' . $val['ope']}($data, $val);
			if ($this->_debug)
				echo 'Caisse: ' . $data['solde'] . ', Pf: ' . $data['pf'] . '<br />';
			$this->func_fin_mois($data, $val);
		}
		
	    if ($this->_debug)
		{
		    $totalProfit += $data['profit'];
		    $capital_club = 0;
	        foreach ($data['cotisation_membre'] as $membre => $info)
		        $capital_club += $info['value'];
			echo 'Perf: ' . ($totalProfit / $capital_club * 100) . '%<br />';
		}
	}

	/* TODO : Check if Zend Date can be used instead of thoses wonderful home made functions */
    private function getPrevMonth($date)
    {
        $year = date("Y", strtotime($date));
        $month = date("n", strtotime($date)) - 1;
        if ($month == 0) {
            $month = 12;
            $year = $year - 1;
        }
        return date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
    }
    
    private function getNextMonth($date)
    {
        $year = date("Y", strtotime($date));
        $month = date("n", strtotime($date)) + 1;
        if ($month == 0) {
            $month = 12;
            $year = $year - 1;
        }
        return date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
    }

    private function getFirstDay($date)
    {
        preg_match('/(\d{4})-(\d{2})-(\d+)/', $date, $matches);
        return date('Y-m-d', mktime(0, 0, 0, $matches[2], 1, $matches[1]));
    }
    
    private function getLastDay($date)
    {
        preg_match('/(\d{4})-(\d{2})-(\d{2})/', $date, $matches);
        $result = strtotime("{$matches[1]}-{$matches[2]}-01");
        $result = strtotime('-1 second', strtotime('+1 month', $result));
        return date('Y-m-d', $result);
    }

    public function getDateFromKey($key)
    {
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $key, $matches))
            return ($matches[1]);
        return (NULL);
    }
    
    public function getTreasuryDate($flag)
    {
    	$dates = $this->getTreasuryDates();
    	if (isset($dates[$flag]))
    		return ($dates[$flag]);
    	throw new Ivc_Exception(Model_Treasury_Treasury::BAD_PARAM, Zend_Log::CRIT);
    }
    
    public function getTreasuryDates()
    {
    	if (!$this->_curTreasuryName)
    		$this->_curTreasuryName = $this->getLastDay($this->getDate());
        $startDate = $this->getFirstDay($this->_curTreasuryName);
        
        if ($startDate < $this->_creationDate)
        	$startDate = $this->_creationDate;
        
        $endDate = $this->_curTreasuryName;
        $list = $this->getTreasuryPendingValidationList();
    	if (count($list))
    		$startOpenDate = $this->getFirstDay(key($list));
    	else
    		$startOpenDate = $this->getFirstDay($this->getDate());
    	
    	if ($startOpenDate < $this->_creationDate)
    		$startOpenDate = $this->_creationDate;
    	
    	$endOpenDate = $this->getLastDay($this->getDate());
    	$data = $this->getData();
        return (array('startDate' => $startDate, 'endDate' => $endDate,
        			  'startOpenDate' => $startOpenDate, 'endOpenDate' => $endOpenDate,
        			  'treasuryName' => $this->_curTreasuryName, 'curDate' => $this->getDate(), 'status' => $data['status']));
    }
    
    /**
     * Function that get a treasury. with no parameters, the last treasury is returned. If a date is set
     * it will return the treasury of the period.
     * 
     * getTreasury will run the asked period calculation or get it from the cache.
     * 
     */
    public function getData($date = NULL) // By date
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
        if ($date)
            $this->_curTreasuryName = $this->getLastDay($date);
        else if (!$this->_curTreasuryName)
            $this->_curTreasuryName = $this->getLastDay($this->getDate());
        if ($this->_isBeforeClubCreation($this->_curTreasuryName) || $this->_curTreasuryName > $this->getLastDay($this->getDate())) // Check min and max date
        		throw new Ivc_Exception(self::TREASURY_DOES_NOT_EXIST, Zend_Log::WARN);
        if (!isset($this->_bilanList[$this->_curTreasuryName]))
        {
            $cache = Ivc_Cache::getInstance();
            $key = "bilan" . $this->_curTreasuryName;
            $key = str_replace("-", "_", $key);
            if (($bilan = $cache->load(Ivc_Cache::SCOPE_CLUB, $key)) === false)
            {
                $bilan = $this->calculateMonth($this->_curTreasuryName);
                // set first validation
                $prevBilan = $this->getMapper()->fetchPrevBilan($this->_curTreasuryName);
                if ($bilan['status'] == 'validation' && ($prevBilan === null || $prevBilan['status'] === "closed"))
                	$bilan['status'] = 'firstValidation';
                
                $cache->save($bilan, Ivc_Cache::SCOPE_CLUB, $key);
            }
            $this->_bilanList[$this->_curTreasuryName] = $bilan;
        }

        return ($this->_bilanList[$this->_curTreasuryName]);
    }
    
    public function setActions(&$data)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
        $actions = new Model_Treasury_TreasuryAction($this);
        $actions->setActions($data);
    }
    
    private function sendToCurrentDate($list)
    {
    	$lastDay = $this->getLastDay($this->getDate());
        $out = array(); /* TODO : find a better way to do that */
        foreach ($list as $date => $id)
        	if ($lastDay >= $date)
        		$out[$date] = $id;
       	return ($out);
    }
    
    /**
     * Function to get a list off the club balance sheet. a balance sheet is the last day of the month
     *
     */
    public function getTreasuryList($full = FALSE)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;

        $cache = Ivc_Cache::getInstance();
        $bilanList = null;
        if ($this->_bilanDatesList)
        	return($this->sendToCurrentDate($this->_bilanDatesList));


        if (($this->_bilanDatesList = $cache->load(Ivc_Cache::SCOPE_CLUB, 'bilanList')) === false)
        {
            $this->_bilanDatesList = $this->getMapper()->fetchBilanList();
            $cache->save($this->_bilanDatesList, Ivc_Cache::SCOPE_CLUB, 'bilanList');
        }
        return($this->sendToCurrentDate($this->_bilanDatesList));
    }
    
    public function getTreasuryListByYear()
    {
    	$list = $this->sendToCurrentDate($this->getTreasuryList());
		$data = array();
    	foreach (array_reverse($list) as $date => $id)
    	{
    		preg_match('/(\d{4})-\d{2}-\d{2}/', $date, $match);
    		$trData = $this->getData($date);
			$data[$match[1]][] = array("date" => $date, "status" => $trData['status']);
    	}
    	return ($data);
    }
    
    /**
     * Function to get a list off the club balance sheet that need validation in order to be closed
     *
     */
    public function getTreasuryPendingValidationList()
    {
       if (!$this->getAcl()->ivcAllowed($this, 'validatePeriod'))
            throw new Ivc_Acl_Exception;

        $cache = Ivc_Cache::getInstance();
        $bilanList = null;
        if ($this->_bilanPendingList)
        	return($this->sendToCurrentDate($this->_bilanPendingList));

        if (($this->_bilanPendingList = $cache->load(Ivc_Cache::SCOPE_CLUB, 'bilanPendingList')) === false)
        {
            $this->_bilanPendingList = $this->getMapper()->fetchBilanPendingList();
            $cache->save($this->_bilanPendingList, Ivc_Cache::SCOPE_CLUB, 'bilanPendingList');
        }
        return($this->sendToCurrentDate($this->_bilanPendingList));
    }
    
	public function getClosedTreasuryList()
    {
       if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;

        $cache = Ivc_Cache::getInstance();
        $bilanList = null;
        if ($this->_bilanClosedList)
        	return($this->sendToCurrentDate($this->_bilanClosedList));

        if (($this->_bilanClosedList = $cache->load(Ivc_Cache::SCOPE_CLUB, 'bilanClosedList')) === false)
        {
            $this->_bilanClosedList = $this->getMapper()->fetchBilanClosedList();
            $cache->save($this->_bilanClosedList, Ivc_Cache::SCOPE_CLUB, 'bilanClosedList');
        }
        return($this->sendToCurrentDate($this->_bilanClosedList));
    }
    
    public function getMembersStats($date = null)
    {
    	if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
        $treasury = $this->getData($date);
        return ($treasury['memberStats']);
    }
    
    public function getMemberStats($memberId, $date = null)
    {
    	if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
        $treasury = $this->getData($date);
        if (!isset($treasury['memberStats'][$memberId]))
        	return (array('value' => 0, 'paid' => 0, 'shareP' => 0, 'unitNb' => 0, 'perfP' => 0));
        return ($treasury['memberStats'][$memberId]);
    }
    
    public function getClubStats($date = null)
    {
    	if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
        $data = $this->getData($date);
        $stats = array();
        $stats['perf'] = round($data['perf'], 2);
        $stats['solde'] = round($data['solde'], 2);
        $stats['pf'] = round($data['pf'], 2);
        $stats['profit'] = round($data['profit'], 2);
        $stats['profitTotal'] = round($data['total_profit'], 2);
        $stats['capital'] = round($data['capital'], 2);
        $stats['unit'] = round($data['unit'], 2);
        $stats['unitNb'] = round($data['unit_nb'], 2);
        $stats['status'] = $data['status'];
        return ($stats);
    }
    
    public function checkDate($date)
    {
    	//echo "Check Date<br />";
        $tr = $this->getData($date);

        if ($tr === null)
        {
            $this->getMessageInstance()->push(Ivc_Message::ERROR, self::TREASURY_DOES_NOT_EXIST);
            return (1);
        }
        if ($tr['status'] === "closed")
        {
            $this->getMessageInstance()->push(Ivc_Message::ERROR, self::TREASURY_CLOSED);
            return (1);
        }
        return (0);
    }
    
    private function cleanCache()
    {
    	$cache = Ivc_Cache::getInstance();
    	$cache->cleanClub();
    	return ($this);	
    }
    
    
    public function editBuy($inputData)
    {
    	if (!$this->getAcl()->ivcAllowed($this, 'editBuy'))
    		throw new Ivc_Acl_Exception;
    	$force = false;
    	$data = array();
    	$data['stock_id'] = $inputData['stock_id'];
    	$data['date'] = $inputData['date'];
    	$data['price'] = $inputData['price'];
    	$data['shares'] = $inputData['shares'];
    	$data['fees'] = $inputData['old_fees'];
    	$data['old_price'] = $inputData['old_price'];
    	$data['old_shares'] = $inputData['old_shares'];
    	$data['old_fees'] = $inputData['old_fees'];
    	$data['transaction_id'] = $inputData['transaction_id'];
    	if (isset($inputData['force']) && $inputData['force'] === true)
    		$force = true;
    	if ($this->checkDate($data['date']))
    		return ($this);
    	$treasury = $this->getData($data['date']);
    	
    	if ($force || $treasury['solde'] - ($data['price'] * $data['shares'] + $data['fees'])
    				                     + ($data['old_price'] * $data['old_shares'] + $data['old_fees'])>= 0)
    	{
    		$data['type'] = 'buy';
    		$this->getMapper()->updateTransaction(array('transaction_id' => $data['transaction_id'],
    																	 'price' => $data['price'],
    																	 'shares' => $data['shares'],
    																	 'fees' => $data['fees']));
    		$this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::BUY_EDITED);
    		return ($this->cleanCache());
    	}
    	else
    		$this->getMessageInstance()->push(Ivc_Message::WARNING, self::NOT_ENOUGH_CASH);
    	return ($this);
    }
    
    /**
     * Add a buy event to the treasury
     * @param array $inputData
     * $inputData include :<br />
     * 'stock_id' : name of the share must fit into DB VARCHAR(65)<br />
     * 'price' : positive float<br />
     * 'date' : std DB format, not in the future<br />
     * 'shares' : positive integer<br />
     * 'fees' : transaction fees<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Checks :<br />
     * Warning if not enough money
     */
    public function addBuy($inputData)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'addBuy'))
            throw new Ivc_Acl_Exception;
        $force = false;
        $data = array();
        $data['price'] = $inputData['price'];
        $data['shares'] = $inputData['shares'];
        $data['date'] = $inputData['date'];
        $data['fees'] = $inputData['fees'];
        $data['stock_id'] = $inputData['stock_id'];
        if (isset($inputData['force']) && $inputData['force'] === true)
            $force = true;
        
        if ($this->checkDate($data['date']))
            return ($this);
        $treasury = $this->getData($data['date']);
        if ($force || $treasury['solde'] - ($data['price'] * $data['shares'] + $data['fees']) >= 0)
        {
            $data['type'] = 'buy';
            // Insert DB
            $this->getMapper()->saveEvent('treasury_transactions', $data);
            // Clear cache
            $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::BUY_ADDED);
            return ($this->cleanCache());
        }
        else
            $this->getMessageInstance()->push(Ivc_Message::WARNING, self::NOT_ENOUGH_CASH); // WARNING, 'Not enough money to buy :' . $data['value'] * $data['quantity']
        return ($this);
    }
    
    /**
     * Dell a buy event from the treasury
     * @param array $inputData
     * $inputData include :<br />
     * 'id' : id of the entry to delete<br />
     * 
     * Checks :<br />
     * Error if the id don't exist or don't belong to the current club
     */
    public function delBuy($inputData)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'delBuy'))
            throw new Ivc_Acl_Exception;
        if (isset($inputData['transaction_id']))
            $id = $inputData['transaction_id'];
        else
            throw new Ivc_Exception(self::NO_ID_SPECIFIED, Zend_Log::CRIT);

        // Check bilan dates
        $buyEntry = $this->getMapper()->fetchEvent('treasury_transactions', $id); //Exception sent if can't get event
        if ($this->checkDate($buyEntry['date']))
            return ($this);
        // del buy
        $this->getMapper()->delEvent('treasury_transactions', $id); //Exception sent if can't del event
        // Clear cache
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::BUY_DELETED);
        return ($this->cleanCache());
    }
    
     /**
     * Add a sell event to the treasury
     * @param array $inputData
     * $inputData include :<br />
     * 'stock_id' : name of the share must fit into DB VARCHAR(65)<br />
     * 'price' : positive float<br />
     * 'date' : std DB format, not in the future<br />
     * 'shares' : positive integer<br />
     * 'fees' : transaction fees<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Also create a profit distribution entry
     * 
     * Checks :<br />
     * 
     */
    public function addSell($inputData)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'addSell'))
            throw new Ivc_Acl_Exception;
        $force = false;
        $data = array();
        $data['stock_id'] = $inputData['stock_id'];
        $data['price'] = $inputData['price'];
        $data['shares'] = $inputData['shares'];
        $data['date'] = $inputData['date'];
        $data['fees'] = $inputData['fees'];
        if (isset($inputData['force']) && $inputData['force'] === true)
            $force = true;
            
        // Warning : Check that share is in portfolio ?
        // Warning : Check quantity ?
        if ($this->checkDate($data['date']))
            return ($this);
        if ($force || 42)
        {
            $data['type'] = 'sell';
            $this->_lastInsertedId = $this->getMapper()->saveEvent('treasury_transactions', $data);
            $data['force'] = true; /* TODO : distribute anyway. do it in two steps ? */
            $data['transaction_id'] = $this->getLastInsertedId();
            $this->addProfitsDistribution($data);
            // Clear cache
            $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::SELL_ADDED);
            return ($this->cleanCache());
        }
        $this->getMessageInstance()->push(Ivc_Message::WARNING, 'message');
        return ($this);
    }
    
    private function addTransacCashLink($transaction_id, $cashflow_id)
    {
        $data = array('transaction_id' => $transaction_id , 'cashflow_id' => $cashflow_id);
        $this->getMapper()->saveLink('treasury_transac_cash', $data);
    }
    
    /**
     * Add a sell profits distribution event to the treasury
     * @param array $inputData
     * $inputData include :<br />
     * 'transaction_id' : id of the target transaction<br />
     * 'users_id' : array of user choices Ex:<br />
     * $inputData['users_id'][110] = 'credit';<br />
     * $inputData['users_id'][111] = 'debit';<br />
     * $inputData['users_id'][112] = 'credit';<br />
     * if no user array suplied, credit users<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Checks :<br />
     * Warning if a member reach is maximum amount of credit
     */
    private function addProfitsDistribution($data)
    {
        $force = false;
        if (isset($data['force']) && $data['force'] === true)
            $force = true;
        
        /* TODO : if no user array suplied, credit users.
         * Not accurate must run treasury simulation to get member list */
           $members = $this->getMembers($data['date']);
        if (!isset($data['users']))
        {
            $members = $this->getMembers($data['date']);
            $data['users_id'] = array();
            foreach ($members as $member_id => $info)
                $data['users_id'][$member_id] = 'credit';
        }
        // Checks ?
        // First run to simulate un get message if there is some
        if (!$force)
        {
            foreach ($data['users_id'] as $user => $action)
            {
                $val = 42; /* TODO : cal member part  and run checks*/
                $tmp = array();
                $tmp['date'] = $data['date'];
                $tmp['club_user_id'] = $user;
                $tmp['value'] = $val; 
                $tmp['comment'] = "$action $val (lol) to " . $members[$user]->first_name;
                if ($action === 'credit')
                    $this->addCredit($tmp, true);
                elseif ($action === 'debit')
                    $this->addDebit($tmp, true);
            }
        }
        // Second run if everithing is ok or forced
        if ($force || !$this->getMessageInstance()->getLevel())
        {
            foreach ($data['users_id'] as $user => $action)
            {
                $tmp = array();
                $tmp['date'] = $data['date'];
                $tmp['club_user_id'] = $user;
                $tmp['comment'] = "$action Dyn to ";// . $members[$user]->first_name;
                $tmp['force'] = true;
                if ($action === 'credit')
                    $this->addTransacCashLink($data['transaction_id'], $this->addCredit($tmp)->getLastInsertedId());
                elseif ($action === 'debit')
                    $this->addTransacCashLink($data['transaction_id'], $this->addDebit($tmp)->getLastInsertedId());
            }
            if ($this->getMessageInstance()->getLevel() < 2)
            {
                $this->getMessageInstance()->flush(); // clean previous warnings
                $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::PROFIT_DISTRIBUTED);
                $this->cleanCache();
            }
        }
        return ($this);
    }
    
   	public function editProfitsDistribution($inputData)
   	{
   		
   	}
   	
	private function delProfitsDistribution($transaction_id)
   	{
   	}
    
   	public function editSell($inputData)
   	{
   		if (!$this->getAcl()->ivcAllowed($this, 'editSell'))
   			throw new Ivc_Acl_Exception;
   		$force = false;
   		$data = array();
   		$data['price'] = $inputData['price'];
   		$data['shares'] = $inputData['shares'];
   		$data['date'] = $inputData['date'];
   		$data['fees'] = $inputData['fees'];
   		$data['transaction_id'] = $inputData['transaction_id'];
   		if (isset($inputData['force']) && $inputData['force'] === true)
   			$force = true;
   		
   		// Warning : Check that share is in portfolio ?
   		// Warning : Check quantity ?
   		if ($this->checkDate($data['date']))
   			return ($this);
   		if ($force || 42)
   		{
   			$this->getMapper()->updateTransaction($data);
   			$this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::SELL_EDITED);
   			return ($this->cleanCache());
   		}
   		$this->getMessageInstance()->push(Ivc_Message::WARNING, 'message');
   		return ($this);
   	}
   	
    /**
     * Dell a sell event from the treasury
     * @param array $inputData
     * $inputData include :<br />
     * 'transaction_id' : id of the entry to delete<br />
     * 
     * Also delete sell profits distribution
     * Checks :<br />
     * Error if the id don't exist or don't belong to the current club
     */
    public function delSell($inputData)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'delSell'))
            throw new Ivc_Acl_Exception;
        if (isset($inputData['transaction_id']))
            $id = $inputData['transaction_id'];
        else
            throw new Ivc_Exception(self::NO_ID_SPECIFIED, Zend_Log::CRIT);
        // Check bilan dates
        $sellEntry = $this->getMapper()->fetchEvent('treasury_transactions', $id);  //Exception sent if can't get event
        if ($this->checkDate($sellEntry['date']))
            return ($this);
        // del sell
        $this->getMapper()->delEvent('treasury_transactions', $id);  //Exception sent if can't delete event
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::SELL_DELETED);
        return ($this->cleanCache());
    }
    
    /**
     * Add a credit event to the treasury
     * @param array $inputData
     * $inputData include :<br />
     * 'club_user_id' : club_user_id can be NULL for credit from nobody<br />
     * 'value' : value of the credit<br />
     * 'comment' : user defined comment<br />
     * 'date' : date<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Checks :<br />
     * Warning if a member reach is maximum amount of credit
     */
    public function addCredit($inputData, $simulate = false)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'addCredit'))
            throw new Ivc_Acl_Exception;
        if ($this->checkDate($inputData['date']))
            return ($this);
        /* TODO : add club_user_id = NULL support */
        $data = array();
        $force = false;
        if (isset($inputData['force']) && $inputData['force'] === true)
        {
            $force = true;
        }
        $data['club_user_id'] = $inputData['club_user_id'];
        if (isset($inputData['value']))
            $data['value'] = $inputData['value'];
        $data['comment'] = $inputData['comment'];
        $data['date'] = $inputData['date'];
        
        if (isset($inputData['value'])) // No need to check for 'value' = NULL (profits dynamic calculation)
        {
            $treasury = $this->getData();
        	/* TODO : add WARNING user share calc 5500 */
        }

        if ($force || !$this->getMessageInstance()->getLevel())
        {
            $data['type'] = 'credit';
            if (!$simulate)
                $this->_lastInsertedId = $this->getMapper()->saveEvent('treasury_cashflow', $data);
            // Clear cache
            $this->getMessageInstance()->flush();
            $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::CREDIT_ADDED);
            $this->cleanCache();
        }
        return ($this);  
    }
    
    public function addContribution($inputData, $simulate = false)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'addCredit'))
            throw new Ivc_Acl_Exception;
        if ($this->checkDate($inputData['date']))
        	return ($this);
        if (!$this->getMemberId($inputData['club_user_id'],$inputData['date']))
        {
        	echo "Member Id don't exist<br />";
        	return ($this);
        }

        /* TODO : add club_user_id = NULL support */
        $data = array();
        $force = false;
        if (isset($inputData['force']) && $inputData['force'] === true)
        {
            $force = true;
        }
        $data['club_user_id'] = $inputData['club_user_id'];
        if (isset($inputData['value']))
            $data['value'] = $inputData['value'];
        $data['comment'] = $inputData['comment'];
        $data['date'] = $inputData['date'];
        
        if (isset($inputData['value'])) // No need to check for 'value' = NULL (profits dynamic calculation)
        {
            //$treasury = $this->getTreasury();
        	/* TODO : add WARNING user share calc 5500 */
        }

        if ($force || !$this->getMessageInstance()->getLevel())
        {
            $data['type'] = 'contribution';
            if (!$simulate)
                $this->_lastInsertedId = $this->getMapper()->saveEvent('treasury_cashflow', $data);
            // Clear cache
            $this->getMessageInstance()->flush();
            $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::CONTRIBUTION_ADDED);
            $this->cleanCache();
        }
        //echo "okGood";
        return ($this);  
    }
    
    public function editCredit($inputData)
    {
    	
    }
    
    public function editContribution($inputData)
    {
    	$this->editCredit($inputData);
    }
    
    /**
     * Dell a credit event from the treasury
     * @param array $inputData
     * $inputData include :<br />
     * 'id' : id of the entry to delete<br />
     * 
     * Checks :<br />
     * Error if the id don't exist or don't belong to the current club
     */
    public function delCredit($inputData)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'delCredit'))
            throw new Ivc_Acl_Exception;
        if (isset($inputData['id']))
            $id = $inputData['id'];
        else
            throw new Ivc_Exception(self::NO_ID_SPECIFIED, Zend_Log::CRIT);
            
        $entry = $this->getMapper()->fetchEvent('treasury_cashflow', $id);
        if ($this->checkDate($entry['date']))
            return ($this);
            
        // Check if enough credit before delete
        $this->getMapper()->delEvent('treasury_cashflow', $id);
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::CREDIT_DELETED);
        $this->cleanCache();
    }
    
    /**
     * Dell a credit event from the treasury
     * @param array $inputData
     * $inputData include :<br />
     * 'id' : id of the entry to delete<br />
     * 
     * Alias to delCredit
     */
    public function delContribution($inputData)
    {
    	$this->delCredit($inputData);
    }
    
    /**
     * Add a debit event to the treasury
     * @param array $inputData
     * $inputData include :<br />
     * 'club_user_id' : club_user_id can be NULL for club expence debit or others<br />
     * 'value' : value of the credit<br />
     * 'comment' : user defined comment<br />
     * 'date' : date<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Checks :<br />
     * Warning if a member share or capital get negative
     * Warning if not enough cash
     */
    public function addDebit($inputData, $simulate = false)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'addDebit'))
            throw new Ivc_Acl_Exception;
        if ($this->checkDate($inputData['date']))
            return ($this);
        /* TODO : add club_user_id = NULL support */
        $data = array();
        $force = false;
        if (isset($inputData['force']) && $inputData['force'] === true)
        {
            $data['force'] = true;
            $force = true;
        }
        $data['club_user_id'] = $inputData['club_user_id'];
        if (isset($inputData['value']))
            $data['value'] = $inputData['value'];
        $data['comment'] = $inputData['comment'];
        $data['date'] = $inputData['date'];
            
        if (isset($inputData['value']))
        {
            $treasury = $this->getData();
            // Warning : User share get negative !
            // Warning : not enough cash
            if ($treasury['solde'] - $data['value'] < 0)
                $this->getMessageInstance()->push(Ivc_Message::WARNING, self::NOT_ENOUGH_CASH);
             /* TODO : add user share calc */
            //if ( USER_SHARE)
            //$rtn->push(Ivc_Message::WARNING, 'User share will become negative');
        }

        if ($force || !$this->getMessageInstance()->getLevel())
        {
            $data['type'] = 'debit';
            if (!$simulate)
                $this->_lastInsertedId = $this->getMapper()->saveEvent('treasury_cashflow', $data);
            // Clear cache
            $this->getMessageInstance()->flush();
            $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::DEBIT_ADDED);
            $this->cleanCache();
        }
        return ($this);
    }
    
    public function editDebit()
    {
    	
    }
    
    /**
     * Dell a debit event from the treasury
     * @param array $inputData
     * $inputData include :<br />
     * 'id' : id of the entry to delete<br />
     * 
     * Checks :<br />
     * Error if the id don't exist or don't belong to the current club
     */
    public function delDebit($inputData)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'delDebit'))
            throw new Ivc_Acl_Exception;
        if (isset($inputData['id']))
            $id = $inputData['id'];
        else
            throw new Ivc_Exception(self::NO_ID_SPECIFIED, Zend_Log::CRIT);
        $entry = $this->getMapper()->fetchEvent('treasury_cashflow', $id);
        if ($this->checkDate($entry['date']))
            return ($this);
        $this->getMapper()->delEvent('treasury_cashflow', $id); // Exception if can't del event

        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::DEBIT_DELETED);
        return ($this->cleanCache());
    }
    
    /**
     * Add a dividend event to the treasury
     * @param array $inputData
     * $inputData include :<br />
     * 'stock_id' : name of the share must fit into DB VARCHAR(65)<br />
     * 'price' : positive float<br />
     * 'date' : std DB format, not in the future<br />
     * 'shares' : positive integer<br />
     * 'fees' : transaction fees<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Also create a profit distribution entry
     * 
     * Checks :<br />
     * 
     */
    public function addDividend($inputData)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'addDividend'))
            throw new Ivc_Acl_Exception;
        if ($this->checkDate($inputData['date']))
            return ($this);
        $force = false;
        $data = array();
        $data['stock_id'] = $inputData['stock_id'];
        $data['price'] = $inputData['price'];
        $data['shares'] = $inputData['shares'];
        $data['date'] = $inputData['date'];
        $data['fees'] = $inputData['fees'];
        if (isset($inputData['force']) && $inputData['force'] === true)
            $force = true;
        
        $data['type'] = 'dividend';
        // Insert DB
        $this->_lastInsertedId = $this->getMapper()->saveEvent('treasury_transactions', $data);
        $data['force'] = true; /* TODO : distribute anyway. do it in two steps ? */
        $data['transaction_id'] = $this->getLastInsertedId();
        $this->addProfitsDistribution($data);
        // Clear cache
        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::DIVIDEND_ADDED);
        return ($this->cleanCache());
    }
    
    public function editDividend($inputData)
    {
    	
    }
    
    public function delDividend($inputData)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'delDividend'))
            throw new Ivc_Acl_Exception;
        if (isset($inputData['id']))
            $id = $inputData['id'];
        else
            throw new Ivc_Exception(self::NO_ID_SPECIFIED, Zend_Log::CRIT);
        $entry = $this->getMapper()->fetchEvent('treasury_transactions', $id);
        if ($this->checkDate($entry['date']))
            return ($this);
        $this->getMapper()->delEvent('treasury_transactions', $id); // Exception if can't del event

        $this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::DEBIT_DELETED);
        return ($this->cleanCache());
    }
    
    public function addReevaluation($inputData)
    {
    	if (!$this->getAcl()->ivcAllowed($this, 'addReevaluation'))
    		throw new Ivc_Acl_Exception;
    	if ($this->checkDate($inputData['date']))
    		return ($this);
    	
    	$data = array();
    	$data['date'] = $inputData['date'];
    	$data['stocks'] = $inputData['stocks'];
    	
    	$reevVal = 0;
    	$stocks = $this->getPortfolio()->getReevaluationData();
    	foreach ($stocks as $id => $stock)
    		$reevVal += ($data['stocks'][$id]['lastPrice'] * $stock['shares']) -  ($stock['costPrice'] * $stock['shares']);
    	
    	$this->getMessageInstance()->push(Ivc_Message::SUCCESS, "ok");
    }
    
	public function delReevaluation($data)
    {
        
    }
    
    public function addQuit($data)
    {
        
    }
    
	public function delQuit($data)
    {
        
    }
    
    public function addNewMember($data)
    {
        
    }
    
    public function validatePeriod($date)
    {
    	$date = $this->getLastDay($date);
    	if (!$this->getAcl()->ivcAllowed($this, 'validatePeriod'))
    		throw new Ivc_Acl_Exception;
    	$bilan = $this->getMapper()->fetchBilan($date);
    	$prevBilan = $this->getMapper()->fetchPrevBilan($date);
    	
    	if ($bilan['status'] !== "validation")
    		throw new Ivc_Exception(self::NOT_A_VALIDATION_STATUS, Zend_Log::WARN);
    	if ($prevBilan === null || $prevBilan['status'] === "closed")
    	{
    		$contrib = new Model_Treasury_Contribution(array('date' => $date));
    		$contrib->setTreasuryName($date);
    		if ($contrib->checkContribution() === true) // Also check reevaluation
    		{
    			$bilan['status'] = 'valid';
    			$this->getMapper()->saveBilan($bilan, $date);
    			$this->getMessageInstance()->push(Ivc_Message::SUCCESS, self::PERIOD_CLOSED);
    			$this->cleanCache();
    			$this->calculateMonth($date);
    		}
    		else
    			$this->getMessageInstance()->push(Ivc_Message::ERROR, self::VALIDATE_CONTRIB_FIRST);
    	}
    	else
    		throw new Ivc_Exception(self::PREV_PERRIOD_NOT_CLOSED, Zend_Log::WARN);
    		

    	//
    }
}

