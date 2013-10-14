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
 * Portfolio mapper. Handle all portfolio's databases requests.
 * 
 * @author		Jonathan Hickson
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Portfolio
 */

class Model_Portfolio_PortfolioMapper
{
    private $dba;
    private $clubId;
    
    public function __construct ($clubId)
	{
	    $this->clubId = $clubId;
	    $this->dba = Zend_Db_Table::getDefaultAdapter();
	}
	
    public function fetchPortfolio()
    {
        $list = array();
        $select = $this->dba->select()
					->from('treasury_transactions')
					->joinUsing('stocks', 'stock_id')
					->where('treasury_transactions.club_id = ?', $this->clubId)
					->order('date ASC')
					->order('transaction_id ASC');
		$rowset = $this->dba->fetchAll($select);
		foreach ($rowset as $row)
		{
		    if (!isset($list[$row['stock_id']]))
		    {
		        $list[$row['stock_id']] = array('history' => array(),
		        								'symbol' => $row['symbol'],
		                                        'name' => $row['name'],
		        								'currency' => $row['currency'],
		        								'stock_id' => intval($row['stock_id']));
		    }
		    $list[$row['stock_id']]['history'][] = array('date' => $row['date'],
		    											'ope' => $row['type'],
		    											'price' => floatval($row['price']),
		                                                'shares' => floatval($row['shares']),
		                                                'fees' => floatval($row['fees']),
		                                                'id' => intval($row['transaction_id']));
		}
        return ($list);
    }
    
    public function fetchPortfolioToDate($date)
    {
    	$list = array();
    	$select = $this->dba->select()
    	->from('treasury_transactions')
    	->joinUsing('stocks', 'stock_id')
    	->where('treasury_transactions.club_id = ?', $this->clubId)
    	->where('date <= ?', $date)
    	->order('date ASC')
    	->order('transaction_id ASC');
    	$rowset = $this->dba->fetchAll($select);
    	foreach ($rowset as $row)
    	{
    		if (!isset($list[$row['stock_id']]))
    		{
    			$list[$row['stock_id']] = array('history' => array(),
    					'symbol' => $row['symbol'],
    					'name' => $row['name'],
    					'currency' => $row['currency'],
    					'stock_id' => intval($row['stock_id']));
    		}
    		$list[$row['stock_id']]['history'][] = array('date' => $row['date'],
    				'ope' => $row['type'],
    				'price' => floatval($row['price']),
    				'shares' => floatval($row['shares']),
    				'fees' => floatval($row['fees']),
    				'id' => intval($row['transaction_id']));
    	}
    	return ($list);
    }
    
    public function findStockTransacDataFromTransactionId($transacId)
    {
    	$select = $this->dba->select()
    				->from('treasury_transactions')
    				->where('transaction_id = ?', $transacId)
    				->where('club_id = ?', $this->clubId);
    	$rowset = $this->dba->fetchAll($select);
    	if (count($rowset))
    		return ($rowset[0]);
    	return (null);
    }
    
}
?>