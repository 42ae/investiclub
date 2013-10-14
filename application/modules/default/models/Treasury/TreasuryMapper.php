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
 * The treasuryMapper is only used by Treasury class. It handle all database access.
 * 
 * @author		Jonathan Hickson
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Treasury
 */


/* TODO : Add treasury_balance_sheet.status to MysqlWorkbench
		  Add treasury_capital_users.paid and Edit treasury_capital_users.value*/

class Model_Treasury_TreasuryMapper
{
    private $dba;
    private $clubId;
    private $inc;
    
    public function __construct ($clubId)
	{
	    $this->clubId = $clubId;
	    $this->dba = Zend_Db_Table::getDefaultAdapter();
	}
	
	/**
	 * Function that fetch treasury events from multiple tables. A start and End date can be used
	 * to set a period. 
	 *
	 * @param array $events list of retrived events
	 * @param date $startDate is the start date of a period
	 * @param date $endDate is the start date of a period
	 */
	public function initEvents(&$events, $startDate = NULL, $endDate = NULL)
	{
		if ($endDate === NULL)
			$endDate = "2011-12-05"; /* TODO : get it correctly */
		if ($startDate === NULL)
			$startDate = "0";
		$inc = 0;
		$events = array();
		
		// Get cotisation
		$select = $this->dba->select()
					->from("treasury_cashflow")
					->where('club_id = ?', $this->clubId)
					->where('type = "credit" OR type = "contribution"') /* TODO : fix this db compatibility pb  */
					->where('club_user_id IS NOT NULL')
					->where('value IS NOT NULL')
					->where('date >= ?',$startDate)
					->where('date <= ?',$endDate)
					->order('date ASC')
					->order('club_user_id ASC');
		$rowset = $this->dba->fetchAll($select);
		foreach ($rowset as $row)
    	{
    		$date = $row['date'] . "-$inc";
    		$events[$date] = array("ope" => "credit",
    								"id" => intval($row['cashflow_id']),
    								"club_user_id" => intval($row['club_user_id']),
    								"value" => floatval($row['value']));
    		$inc++;
    	}
		// Get sell
		$select = $this->dba->select()
					->from("treasury_transactions")
					->joinUsing('stocks', 'stock_id')
					->where('club_id = ?', $this->clubId)
					->where('type = ?', "sell")
					->where('date >= ?',$startDate)
					->where('date <= ?',$endDate)
					->order('date ASC');
		$rowset = $this->dba->fetchAll($select);
		foreach ($rowset as $row)
    	{
    		$date = $row['date'] . "-$inc";
    		$tmp = array("ope" => "sell",
    						"id" => intval($row['transaction_id']),
    		                "stock_id" => intval($row['stock_id']),
    						"symbol" => $row['symbol'],
    						"price" => floatval($row['price']),
    						"fees" => floatval($row['fees']),
    						"shares" => intval($row['shares']));
    	    $transacSelect = $this->dba->select()
    	            ->from('treasury_transac_cash')
    	            ->joinUsing('treasury_cashflow', 'cashflow_id')
    	            ->where('treasury_transac_cash.transaction_id = ?', $row['transaction_id']);
    	    $transacRowset = $this->dba->fetchAll($transacSelect);
    	    $tmp['users'] = array();
    	    foreach ($transacRowset as $transacRow)
    	        $tmp['users'][$transacRow['club_user_id']] = $transacRow['type'];
    	    $events[$date] = $tmp;
    		$inc++;
    	}
    	
    	// Get buy
    	$select = $this->dba->select()
					->from("treasury_transactions")
					->joinUsing('stocks', 'stock_id')
					->where('club_id = ?', $this->clubId)
					->where('type = ?', "buy")
					->where('date >= ?',$startDate)
					->where('date <= ?',$endDate)
					->order('date ASC');
		$rowset = $this->dba->fetchAll($select);
		foreach ($rowset as $row)
    	{
    		$date = $row['date'] . "-$inc";
    		$events[$date] = array("ope" => "buy",
    							   "id" => $row['transaction_id'],
    							   "symbol" => $row['symbol'],
    							   "price" => $row['price'],
    							   "shares" => $row['shares'],
    							   "fees" => $row['fees']);
    		$inc++;
    	}
		
		// Get dividends
		$select = $this->dba->select()
					->from("treasury_transactions")
					->joinUsing('stocks', 'stock_id')
					->where('club_id = ?', $this->clubId)
					->where('type = ?', "dividend")
					->where('date >= ?',$startDate)
					->where('date <= ?',$endDate)
					->order('date ASC');
		$rowset = $this->dba->fetchAll($select);
		foreach ($rowset as $row)
    	{
    		$date = $row['date'] . "-$inc";
    		$tmp = array("ope" => "dividend",
    		     		 "id" => $row['transaction_id'],
    		     		 "symbol" => $row['symbol'],
    		     		 "price" => $row['price'],
    		     		 "shares" => $row['shares']);
    		$transacSelect = $this->dba->select()
    	            ->from('treasury_transac_cash')
    	            ->joinUsing('treasury_cashflow', 'cashflow_id')
    	            ->where('treasury_transac_cash.transaction_id = ?', $row['transaction_id']);
    	    $transacRowset = $this->dba->fetchAll($transacSelect);
    	    foreach ($transacRowset as $transacRow)
    	    {
    	        $tmp['users'][$transacRow['club_user_id']] = $transacRow['type'];
    	    }
    	    $events[$date] = $tmp;
    		$inc++;
    	}
    	
	    // Get reevaluation
	    $select = $this->dba->select()
					->from("treasury_revaluation")
					->where('club_id = ?', $this->clubId)
					->where('date >= ?',$startDate)
					->where('date <= ?',$endDate)
					->order('date ASC');
		$rowset = $this->dba->fetchAll($select);
		foreach ($rowset as $row)
    	{
    		$date = $row['date'] . "-$inc";
    		$events[$date] = array("ope" => "reevaluation",
    							   "id" => intval($row['revaluation_id']),
    							   "value" => intval($row['value']),
    							   "comment" => $row['comment']);
    		$inc++;
    	}
    	
		// Get debit
		$select = $this->dba->select()
					->from("treasury_cashflow")
					->where('club_id = ?', $this->clubId)
					->where('club_user_id IS NOT NULL')
					->where('value IS NOT NULL')
					->where('type = ?', "debit")
					->where('date >= ?',$startDate)
					->where('date <= ?',$endDate)
					->order('date ASC');
		$rowset = $this->dba->fetchAll($select);
		foreach ($rowset as $row)
    	{
    		$date = $row['date'] . "-$inc";
    		$events[$date] = array("ope" => "debit",
    							   "id" => $row['cashflow_id'],
    							   "club_user_id" => $row['club_user_id'],
    							   "value" => $row['value'],
    							   "comment" => $row['comment']);
    		$inc++;
    	}
    	
		// Get demission
		$select = $this->dba->select()
					->from("members")
					->where('club_id = ?', $this->clubId)
					->where('departure_date IS NOT NULL')
					->where('departure_date >= ?',$startDate)
					->where('departure_date <= ?',$endDate)
					->order('departure_date ASC');
		$rowset = $this->dba->fetchAll($select);
		foreach ($rowset as $row)
    	{
    		$date = $row['departure_date'] . "-$inc";
    		$events[$date] = array("ope" => "demission", "id" => $row['member_id'], "club_user_id" => $row['member_id']);
    		$inc++;
    	}
    	
	   	ksort($events);
	}
	
	/**
	 * Fetch assessment list from database
	 */
	public function fetchBilanList($date = null)
	{
	    $select = $this->dba->select()
					->from('treasury_balance_sheet')
					->where('club_id = ?', $this->clubId)
					->order('date ASC');
		$rowset = $this->dba->fetchAll($select);
		
		$list = array();
		foreach ($rowset as $row)
		    $list[$row['date']] = $row['balance_sheet_id'];
		return ($list);
	}
	
	/**
	 * Fetch assessment list from database
	 */
	public function fetchBilanPendingList($date = null)
	{
	    $select = $this->dba->select()
					->from('treasury_balance_sheet')
					->where('club_id = ?', $this->clubId)
					->where('status = ?', 'validation')
					->order('date ASC');
		$rowset = $this->dba->fetchAll($select);
		
		$list = array();
		foreach ($rowset as $row)
		    $list[$row['date']] = $row['balance_sheet_id'];
		return ($list);
	}
	
	/**
	 * Fetch assessment list from database
	 */
	public function fetchBilanClosedList($date = null)
	{
	    $select = $this->dba->select()
					->from('treasury_balance_sheet')
					->where('club_id = ?', $this->clubId)
					->where('status = ?', 'closed')
					->order('date ASC');
		$rowset = $this->dba->fetchAll($select);
		
		$list = array();
		foreach ($rowset as $row)
		    $list[$row['date']] = $row['balance_sheet_id'];
		return ($list);
	}
	
	/**
	 * Fetch assessment from database and return an array
	 */
	public function fetchBilan($date)
	{
		$select = $this->dba->select()
					->from('treasury_balance_sheet')
					->where('club_id = ?', $this->clubId)
					->where('date = ?',$date);
		$row = $this->dba->fetchRow($select);
		if ($row)
		{
			$data = array();
			$data['solde'] = floatval($row['balance']);
			$data['pf'] = floatval($row['portfolio']);
			$data['profit'] = floatval($row['profit']);
			$data['total_profit'] = floatval($row['total_profit']);
			$data['unit'] = floatval($row['unit']);
			$data['unit_nb'] = floatval($row['unit_nb']);
			$data['balance_sheet_id'] = intval($row['balance_sheet_id']);
			$data['status'] = $row['status'];
			$data['date'] = $row['date'];

			// Get eatch member capital
			/* TODO: Use Dbtable dependency for that */
			$select = $this->dba->select()
					->from('treasury_capital_users')
					->where('balance_sheet_id = ?', $row['balance_sheet_id']);
			$capitalRowSet = $this->dba->fetchAll($select);
			$data['cotisation_membre'] = array();
			foreach ($capitalRowSet as $capitalRow)
			{
				$data['cotisation_membre'][$capitalRow['member_id']]['value'] = $capitalRow['value'];
				$data['cotisation_membre'][$capitalRow['member_id']]['paid'] = $capitalRow['paid'];
				//if (!isset($data['profit_membre'][$capitalRow['user_id']])) /* TODO get member capital and profit */
    			//	$data['profit_membre'][$capitalRow['user_id']] = 0;
				//$data['profit_membre'][$capitalRow['user_id']] += $capitalRow['profit'];
			}			
			return ($data);
		}
		return (NULL);
	}
	
	public function fetchPrevBilan($date)
	{
		$select = $this->dba->select()
		->from('treasury_balance_sheet')
		->where('club_id = ?', $this->clubId)
		->where('date < ?',$date)
		->order('date DESC')
		->limit(1);
		$row = $this->dba->fetchRow($select);
		if ($row)
		{
			$data = array();
			$data['solde'] = floatval($row['balance']);
			$data['pf'] = floatval($row['portfolio']);
			$data['profit'] = floatval($row['profit']);
			$data['total_profit'] = floatval($row['total_profit']);
			$data['unit'] = floatval($row['unit']);
			$data['unit_nb'] = floatval($row['unit_nb']);
			$data['balance_sheet_id'] = intval($row['balance_sheet_id']);
			$data['status'] = $row['status'];
			$data['date'] = $row['date'];
	
			// Get eatch member capital
			/* TODO: Use Dbtable dependency for that */
			$select = $this->dba->select()
			->from('treasury_capital_users')
			->where('balance_sheet_id = ?', $row['balance_sheet_id']);
			$capitalRowSet = $this->dba->fetchAll($select);
			$data['cotisation_membre'] = array();
			foreach ($capitalRowSet as $capitalRow)
			{
				$data['cotisation_membre'][$capitalRow['member_id']]['value'] = $capitalRow['value'];
				$data['cotisation_membre'][$capitalRow['member_id']]['paid'] = $capitalRow['paid'];
				//if (!isset($data['profit_membre'][$capitalRow['user_id']])) /* TODO get member capital and profit */
				//	$data['profit_membre'][$capitalRow['user_id']] = 0;
				//$data['profit_membre'][$capitalRow['user_id']] += $capitalRow['profit'];
			}
			return ($data);
		}
		return (NULL);
	}

	/**
	 * 
	 * Save assessment into database in order to use it later for calculation
	 * @param array $data
	 * @param string $date
	 */
	public function saveBilan($data, $date)
	{
	    $bilan_id = 0;
	    $tmp = array("balance" => $data['solde'],
							"portfolio" => $data['pf'],
							"profit" => $data['profit'],
	    					"total_profit" => $data['total_profit'],
	                        "unit" => $data['unit'],
	                        "unit_nb" => $data['unit_nb'],
		                    "status" => $data['status'],
							"club_id" => $this->clubId,
							"date" => $date);
	    $bilanTable = new Zend_Db_Table('treasury_balance_sheet');
	    if (isset($data['balance_sheet_id']))
	    {
	        $tmp['balance_sheet_id'] = $data['balance_sheet_id'];
	        /* TODO : why ? */
	        //$bilan_id = $bilanTable->update($tmp); 
	        $bilanTable->find($data['balance_sheet_id'])->current()->delete();
	        $bilan_id = $bilanTable->insert($tmp);
	    }
	    else
		    $bilan_id = $bilanTable->insert($tmp);
		if (isset($data['cotisation_membre']))
		{
		    $capitalMembreTable = new Zend_Db_Table('treasury_capital_users');
			/* TODO : Insert in one time*/
		    foreach ($data['cotisation_membre'] as $membre => $info)
		    {
			    $tmp = array("balance_sheet_id" => $bilan_id,
			    				"member_id" => $membre,
			    				"value" => $info['value'],
			                    "paid" => $info['paid']);
			    $capitalMembreTable->insert($tmp);
		    }
		}
		return ($bilan_id);
	}
	
    /**
     * 
     * Fetch a treasury event from database. It also check that the id belong to the club
     * 
     * @param string $type : "table_name"
     * @param int $id
     */
    public function fetchEvent($type, $id)
	{
	    $dbTable = new Zend_Db_Table($type);
	    $result = $dbTable->find($id);
	    if (count($result) == 0)
	        throw new Ivc_Exception(Model_Treasury_Treasury::NO_SUCH_ID, Zend_Log::WARN);
        $row = $result->current();
	    if (intval($row['club_id']) != $this->clubId)
	        throw new Ivc_Exception(Model_Treasury_Treasury::NOT_CURRENT_CLUB_ID, Zend_Log::WARN);
        return ($row->toArray());
	}
	
	/**
	 * 
	 * Save event to database. "club_id" is set internaly to provide a better security.
	 * @param string $type : "table_name"
	 * @param unknown_type $data : data to save
	 */
	public function saveEvent($type, $data)
	{
	    $data['club_id'] = $this->clubId;
	    $dbTable = new Zend_Db_Table($type);
        return ($dbTable->insert($data));
	}
	
	public function updateTransaction($data)
	{
		$data['club_id'] = $this->clubId;
		$dbTable = new Zend_Db_Table('treasury_transactions');
		$where = 'transaction_id = ' . $data['transaction_id'];
		unset($data['transaction_id']);
		$dbTable->update($data, $where);
	}
	
	/**
	 * 
	 * Save event link to database.
	 * @param string $type : "table_name"
	 * @param unknown_type $data : data to save
	 */
    public function saveLink($type, $data)
	{
	    $dbTable = new Zend_Db_Table($type);
        return ($dbTable->insert($data));
	}
    
	/**
     * 
     * Fetch a treasury event from database. It also check that the id belong to the club
     * 
     * @param string $type : "table_name"
     * @param int $id
     */
	public function delEvent($type, $id)
	{
	    $dbTable = new Zend_Db_Table($type);
	    $result = $dbTable->find($id);
        if (count($result) == 0)
            throw new Ivc_Exception(Model_Treasury_Treasury::NO_SUCH_ID, Zend_Log::WARN);
        $row = $result->current();
        if (intval($row['club_id']) != $this->clubId)
            throw new Ivc_Exception(Model_Treasury_Treasury::NOT_CURRENT_CLUB_ID, Zend_Log::WARN);
              
        if ($type === 'treasury_transactions' && ($row['type'] === 'sell' || $row['type'] === 'dividend'))
        {
        	$select = $this->dba->select()
					->from('treasury_transac_cash')
					->where('transaction_id = ?', $row['transaction_id']);
			$rowset = $this->dba->fetchAll($select);
			
			$cashTable = new Zend_Db_Table('treasury_cashflow');
			foreach ($rowset as $transacCashRow)
			{
				$where = $cashTable->getAdapter()->quoteInto('cashflow_id = ?', $transacCashRow['cashflow_id']);
				$cashTable->delete($where);
			}
			$transacCashTable = new Zend_Db_Table('treasury_transac_cash');
			$where = $transacCashTable->getAdapter()->quoteInto("transaction_id = ?", $row['transaction_id']);
			$transacCashTable->delete($where);
        }
        $row->delete();
        return (0);
	}
	
	public function fetchContributions($endDate)
	{
	    $list = array();
	    $select = $this->dba->select()
					->from('treasury_cashflow')
					->where('club_id = ?', $this->clubId)
					->where('type = "contribution"')
					->where('date <= ?', $endDate)
					->order('date ASC')
					->order('club_user_id ASC');
		$rowset = $this->dba->fetchAll($select);
		foreach ($rowset as $row)
    	{
    		if (!isset($list[intval($row['club_user_id'])]))
    			$list[intval($row['club_user_id'])] = array();
    		$list[intval($row['club_user_id'])][$row['date']] = floatval($row['value']);
    	}
    	return ($list);   
	}

	public function findContributionId($memberId, $startDate, $endDate)
	{
		$select = $this->dba->select()
					->from('treasury_cashflow')
					->where('club_id = ?', $this->clubId)
					->where('club_user_id = ?', $memberId)
					->where('type = "contribution"')
					->where('date >= ?', $startDate)
					->where('date <= ?', $endDate);
		$rowset = $this->dba->fetchAll($select);
		if (!count($rowset))
			return (null);
		$row = $rowset[0];
		return ($row['cashflow_id']);
	}
}
