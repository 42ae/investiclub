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
 * Charts is the class that handle the club's charts.
 * 
 * @author		Jonathan Hickson
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Charts
 */

class Model_Charts_Charts extends Ivc_Core
{
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
    
	public function getResourceId()
    {
        return ('club:' . $this->getClubId() . ':charts');
    }
    
    private function setAclRules()
    {
        $acl = Zend_Registry::get('Ivc_Acl');
        if ($acl->has($this->getResourceId())) // if already set return
            return;
        $acl->add(new Zend_Acl_Resource($this->getResourceId()));

        // Set Guest and User rules
        $acl->deny(Ivc_Acl::USER, $this, array('list'));
        // Set club default rules
        if ($this->getClubId())
        {
            $acl->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'list');
        }
        // Set dynamic rules, works for external users rights
        Ivc_Acl_Factory::setDynAcl($acl, $this);
    }
    
    
    public function getSymbolTransactionData($inputData)
    {
    	$dba = Zend_Db_Table::getDefaultAdapter();
    	
    	$portfolio = new Model_Portfolio_Portfolio();
    	$quotes = $portfolio->getPortfolioStocks();
    	//Zend_Debug::Dump($quotes[1]);
    	$startDate = '2012-01-05';
    	$endDate = '2012-12-14';
    	
    	
    	$select = $dba->select()
					->from("quotes_historical")
					->where('symbol = ?', $inputData['symbol'])
					->where('date >= ?', $startDate)
					->where('date <= ?', $endDate)
					->order('date ASC');
		$rowset = $dba->fetchAll($select);
		$out = array();
		foreach ($rowset as $row)
    	{
    		$out[$row['date']] = array("open" => floatval($row['open']),
    								   "close" => floatval($row['close']),
    								   "high" => floatval($row['high']),
    								   "low" => floatval($row['low']),
    								   "volume" => intval($row['volume'])
    									);
    	}
    	
    	$select = $dba->select()
					->from("quotes_historical")
					->where('symbol = ?', "^FCHI")
					->where('date >= ?', $startDate)
					->where('date <= ?', $endDate)
					->order('date ASC');
		$rowset = $dba->fetchAll($select);
		
		$firstDate = $rowset[0]['date'];
		$coef = $out[$firstDate]['close'] / $rowset[0]['close'];
    	foreach ($rowset as $row)
    	{
    		$out[$row['date']]["cac40"] = round(floatval($row['close'] * $coef), 2);
    	}
    	
    	foreach ($quotes[1]->getHistory() as $val)
    	{
    		$out[$val['date']]['ope'] = $val;
    	}
    	
    	$prevClose = 0;
    	foreach ($out as $key => &$val)
    	{
    		if (!isset($val['close']))
    			$val['close'] = $prevClose;
    		if (!isset($val['cac40']))
    			$val['cac40'] = 0;
    		
    		$prevClose = $val['close'];
    	}
    	ksort($out);
    	
    	$costPrice = 0;
    	
    	$fb = 0;
    	$shares = 0;
    	$total_transac = 0;
    	$value = 0;
    	$costPrice = 0;
    	$somme_achat = 0;
        $somme_achat_quantity = 0;
    	foreach ($out as $key => &$val)
    	{
    		if (!$fb)
    			$costPrice = $val['close'];
	
    		if (isset($val['ope']) && $val['ope']['ope'] === 'buy')
            {
            	$fb = 1;
                $prix_transac = $val['ope']['price'] * $val['ope']['shares'] + $val['ope']['fees'];
                $total_transac += $prix_transac;
                $value += $prix_transac;
                $shares += $val['ope']['shares'];
                $costPrice = $value / $shares;
                $somme_achat += $prix_transac;
                $somme_achat_quantity += $val['ope']['shares'];
            }
            elseif (isset($val['ope']) && $val['ope']['ope'] === 'sell')
            {
                $prix_transac = $val['ope']['price'] * $val['ope']['shares'] - $val['ope']['fees'];
                $total_transac -= $prix_transac;
                $value -= $prix_transac;
                $shares -= $val['ope']['shares'];
                $costPrice = $value / $shares;
                
            }

            $val['coastprice'] = $costPrice;
            if ($somme_achat_quantity)
    			$val['profits'] = (($somme_achat / $somme_achat_quantity ) - $costPrice);
    		else
    			$val['profits'] = 0;
    	}
    	return ($out);
    }
    
    /**
     * get Stats infos to generate club's graph and stats
     * @param array $inputData
     * $inputData include :<br />
     * 'compVal' : symbol to compare the club to. ex: FCHI<br />
     */
    public function getClubStatsData($inputData = NULL)
    {
    	$list = $this->getTreasury()->getTreasuryList();

    	if (!$inputData)
    		$inputData = array('compVal' => '^FCHI');
    	
    	$stats = array();
    	foreach ($list as $key => $val)
    	{
    		$data = $this->getTreasury()->getData($key);
    		$stats = array_merge($stats, $data['stats']);
    	}
    	
    	// Get start and end dates to generate stats
    	reset($list);
    	$firstBilan = key($list);
    	$startDate = $this->getTreasury()->setTreasuryName($firstBilan)->getTreasuryDate('startDate');
    	end($list);
    	$lastBilan = key($list);
    	$endDate = $this->getTreasury()->setTreasuryName($lastBilan)->getTreasuryDate('endDate');
    	$this->getTreasury()->setTreasuryName(); // Reset to curent treasury
		
    	$cacheId = 'clubStats' . $endDate;
    	if (($statsTmp = $this->getCache()->load(Ivc_Cache::SCOPE_CLUB, $cacheId)) !== false)
    		return ($statsTmp);
    	
    	$data = array('symbol' => $inputData['compVal']);
		$qh = new Model_Portfolio_QuotesHistorical($data);
		$qh->getQuote($startDate, $endDate);
    	
    	$dba = Zend_Db_Table::getDefaultAdapter();
    	$select = $dba->select()
					->from("quotes_historical")
					->where('symbol = ?', $inputData['compVal'])
					->where('date >= ?', $startDate)
					->where('date <= ?', $endDate)
					->order('date ASC');
		$rowset = $dba->fetchAll($select);
		
		$cacFirstVal = 0;
    	foreach ($rowset as $row)
    	{
    		if (!$cacFirstVal)
    			$cacFirstVal = floatval($row['close']);
    		$stats[$row['date']]["cac40"] = $row['close'];
    	}
    	ksort($stats);
    	
    	$unit = 1;
    	$unit_nb = 0;
    	$perf = 0;
    	$total_profit = 0;
    	$capital_club = 0;
    	$cac40 = $cacFirstVal;
    	foreach ($stats as &$val)
    	{
    		if (!isset($val['unit']))
    		{
    			$val['unit'] = $unit;
    			$val['unit_nb'] = $unit_nb;
    			$val['capital_club'] = $capital_club;
    			$val['perf'] = $perf;
    			$val['total_profit'] = $total_profit;
    			$val['profit'] = 0;
    		}
    		else
    		{
    			$val['unit'] = round($val['unit'], 3);
    			$unit = $val['unit'];
    			$unit_nb = $val['unit_nb'];
    			$capital_club = $val['capital_club'];
    			$val['perf'] = round($val['perf'], 3);
    			$perf = $val['perf'];
    			$total_profit = $val['total_profit'];
    		}
    		if (!isset($val['cac40']))
    			$val['cac40'] = $cac40; // Because of wrond dates in portfolio ...
    		else
    			$cac40 = $val['cac40'];
    		$val['comp'] = round($val['cac40'] / $cacFirstVal, 3);
    		$val['compP'] = round(($val['cac40'] - $cacFirstVal) / $cacFirstVal * 100, 3);
    	}
    	$this->getCache()->save($stats, Ivc_Cache::SCOPE_CLUB, $cacheId, 3600);
    	return ($stats);
    } 
}