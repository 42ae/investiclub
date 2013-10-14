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
 * Portfolio Stock. It's the class that store a stock data and statistcs.
 * It is mostly called by the portfolio but it can be invoked directly to get
 * a stock information
 * 
 * @author		Jonathan Hickson
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Portfolio
 * @todo		strat from 0 when shares are all sold - check use of refs
 */
class Model_PortfolioShare extends Ivc_Core
{
    const MAX_UN_INT32 = 4294967295;
    const ENOUGH_SHARES = -1;
    const NOT_IN_LIST = -1;
    
    private $_name;
    private $_symbol;
    private $_stockId;
    private $_calc = null;
    private $_infos = null;
    private $_history = null;
    private $_lastUpdate = null;
    private $_mapper = null;
    private $_list = null;
    
    /**
     * PortfolioStock constructor
     * @param array $data that contain all information about the stock. the minimum is :
     * $data = array('stock_id' => 'XXX') or $data = array('symbol' => 'XXX')
     * @param &$mapperRef = NULL is a ref to the portfolioMapper. It is not mandatory but can reduce resources use.
     * @param &$treasuryRef = NULL is a ref to the treasury. It is not mandatory but can reduce resources use.
     * 
     * Full $data infos looks like : 
     * $data = array('history' => array (
	 *                              array('date' => '2011-02-06', 'ope' => 'buy', 'price' => 308, 'shares' => 5, 'fees' => 10),
	 *                              array('date' => '2011-02-09', 'ope' => 'sell', 'price' => 310, 'shares' => 2, 'fees' => 0),
     *                              array('date' => '2011-02-15', 'ope' => 'buy', 'price' => 306, 'shares' => 3, 'fees' => 0),
     *                              array('date' => '2011-02-18', 'ope' => 'sell', 'price' => 312, 'shares' => 4, 'fees' => 10),
     *                              array('date' => '2011-02-15', 'ope' => 'buy', 'price' => 308, 'shares' => 10, 'fees' => 8)
     *                              ),
     *               'lastUpdate' => array('price' => 308, 'up' => 314, 'high' => 308 'volume' => 9233),
     *               'config' => array('periode_start' => '2011-02-01', 'periode_end' => '2011-02-28'),
     *               'symbol' => 'ILD.PA',
     *               'currency' => 'EUR',
     *               'id' => 42,
     *               );
     */
    public function __construct(array $options = null)
    { 
    	if (is_array($options))
			$this->setOptions($options);
		    
		//if (!$this->_treasury && !$this->_mapper) // Started stand alone
			//new Model_Portfolio_Portfolio();


        //$this->initStock();
        return ($this);
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
    
    public function setStockId($stockId)
    {
    	$this->_stockId = $stockId;
    	return ($this);
    }
    
    public function setTreasuryRef($treasuryRef)
    {
        $this->_treasury = $treasuryRef;
        return ($this);
    }
    
    public function getResourceId()
    {
        return ('club:' . $this->getClubId() . ':portfolio');
    }
     
    private function getMapper()
    {
        if (!$this->_mapper)
            $this->_mapper = new Model_Portfolio_PortfolioMapper($this->getClubId());
        return ($this->_mapper);
    }
    
	public function getStockId()
    {
	    return ($this->_stockId);
	}
    
    public function getSymbol()
    {
	    return ($this->_symbol);
	}
	
    public function getName()
    {
	    return ($this->_name);
	}
	
    public function getCurrency()
    {
	    return ($this->_currency);
	}
	
	public function getLastPrice()
	{
		$tmp = end($this->_history);
		return ($tmp['price']);
	}
    
    private function setData($data = NULL)
    {
        $this->_stockId  = $data['stock_id'];
        if (isset($data['symbol']))
            $this->_symbol = $data['symbol'];
        if (isset($data['name']))
            $this->_name = $data['name'];
        if (isset($data['currency']))
            $this->_currency = $data['currency'];
        if (isset($data['history']))
            $this->_history = $data['history'];
        if (isset($data['lastUpdate']))
            $this->_lastUpdate = &$data['lastUpdate'];
        // ELSE fetch directly from db ?
    }
    
    public function setLastUpdate($lastUpdate = null)
    {
    	if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
            
    	if ($lastUpdate)
        	$this->_lastUpdate = $lastUpdate;
    	else
    		$this->_lastUpdate = array('price' => $this->getLastPrice());
        return ($this);
    }
    
	public function getLastUpdate()
    {
    	if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
            
        if (!$this->_lastUpdate)
	        $this->_lastUpdate = array('price' => $this->getLastPrice());
        return ($this->_lastUpdate);
    }
    
    /**
     * Get the stock information like open,close,high,low and volume values. Also get company informations ...
     * 
     */
    private function initStock()
    {
        // $this->_infos
    }
    
    /**
     * Calculate all stats of the current stock
     * 
     */
    public function calculateAll()
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list')) /* TODO : return should be enough, check with treasury ACL */
            return;
        $this->calculateToId();
        return ($this);
    }
    
    /**
     * Calculate stats of the current stock to Id
     * 
     */
    public function calculateToId($id = NULL)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list')) /* TODO : return should be enough, check with treasury ACL */
            return;
        $total_transac = 0;
        $prix_transac = 0;
        $costPrice = 0;
        $costPriceP = 0;
        $somme_achat = 0;
        $somme_achat_quantity = 0;
        $moy_prix_achat = 0;
        $somme_totale_valorise = 0;
        $value = 0;
        $shares = 0;
        $calc = array();
        $list = &$this->_history;
        $lastUpdate = $this->getLastUpdate();
        $size = count($list);
        $ind = 0;
        $transacListId = 0;
        while ($ind < $size)
        {
        	$costPriceP = $costPrice;
            if ($list[$ind]['ope'] === 'buy')
            {
            	if ($shares == 0) // First or new transac
            	{
            		$transacListId++;
            		//$this->['TransacListNb'] += 1;
            		$prix_transac = $list[$ind]['price'] * $list[$ind]['shares'] + $list[$ind]['fees'];
            		$total_transac = $prix_transac;
            		$value = $prix_transac;
            		$shares = $list[$ind]['shares'];
            		$costPrice = $value / $shares;
            		$somme_achat = $prix_transac;
            		$somme_achat_quantity = $list[$ind]['shares'];
            	}
            	else
            	{
                	$prix_transac = $list[$ind]['price'] * $list[$ind]['shares'] + $list[$ind]['fees'];
                	$total_transac += $prix_transac;
                	$value += $prix_transac;
                	$shares += $list[$ind]['shares'];
                	$costPrice = $value / $shares;
                	$somme_achat += $prix_transac;
                	$somme_achat_quantity += $list[$ind]['shares'];
            	}
            }
            elseif ($list[$ind]['ope'] === 'sell')
            {
                $prix_transac = $list[$ind]['price'] * $list[$ind]['shares'] - $list[$ind]['fees'];
                $total_transac -= $prix_transac;
                $value -= $prix_transac;
                $shares -= $list[$ind]['shares'];
                if ($shares)
                	$costPrice = $value / $shares;
                else
                	$costPrice = $value / $list[$ind]['shares'];
            }

            // Save calculded values
            $list[$ind]['costPrice'] = $costPrice; // buy sell
            $list[$ind]['totalCost'] = $prix_transac; // buy sell
            $list[$ind]['transacListId'] = $transacListId;
            if ($list[$ind]['ope'] === 'sell')
                $list[$ind]['benefice'] = ($list[$ind]['price'] - $list[$ind - 1]['costPrice'] - $list[$ind]['fees'] / $list[$ind]['shares']);
            if ($id && $list[$ind]['id'] == $id)
            {
            	if ($ind > 0)
            		$costPrice = $list[$ind - 1]['costPrice'];
            	else
            		$costPrice = 0;
                if (isset($list[$ind]['benefice']))
                    return (array('profit' => $list[$ind]['benefice'],
                			  	  'revient' => $costPrice,
                              	  'shares' => $list[$ind]['shares']));
                else
                    return (array('revient' => $costPrice,
                              	  'shares' => $list[$ind]['shares']));
            }
            $ind++;
        }
        $somme_totale_valorise = $lastUpdate['price'] * $shares;
        $moy_prix_achat = $somme_achat / $somme_achat_quantity;
        $plus_value = ($moy_prix_achat - $costPrice) * $shares;
        $plus_value_latente = ($lastUpdate['price'] - $costPrice) * $shares; // also : $plus_value_latente = $somme_totale_valorise - $total_transac;
        $plus_value_latente_pma = ($lastUpdate['price'] - $moy_prix_achat) * $shares;

        // Save data
        $calc['shares'] = $shares;
        $calc['val_moy'] = $costPrice; /* TODO : costPrice ? */
        $calc['total_transac'] = $total_transac;
        $calc['total_valorise'] = $somme_totale_valorise;
        $calc['plus_value'] = $plus_value;
        $calc['plus_value_latente'] = $plus_value_latente;
        $calc['plus_value_latente_pma'] = $plus_value_latente_pma;
        $calc['costPrice'] = $costPrice; //$moy_prix_achat;
        $this->_calc = $calc;
        $this->_calc[$transacListId] = $calc;
        /* TODO : Clean or comment not necessary indicators */
    }

    /**
     * Debug method
     * 
     */
    public function printInfos()
    {
        echo $this->_symbol . ' (id: '. $this->_stockId .')<br />';
        echo print_r($this->_lastUpdate) . '<br />';
        echo '<table width=700px>';
        echo '<td>TL id</td>' .
        	'<td>date</td>' .
            '<td>ope</td>' .
            '<td>value</td>' .
            '<td>quantity</td>' .
            '<td>fees</td>' .
            '<td>prix_total</td>' .
            '<td>prix_revient</td>' .
        	'<td>benefices</td>';
        foreach ($this->_history as $val)
        {
            echo '<tr>';
            echo '<td>' . $val['transacListId'] . '</td>' .
            '<td>' . $val['date'] . '</td>' .
            '<td>' . $val['ope'] . '</td>' .
            '<td>' . $val['price'] . '</td>' .
            '<td>' . $val['shares'] . '</td>' .
            '<td>' . $val['fees'] . '</td>' .
            '<td>' . $val['totalCost'] . '</td>' .
            '<td>' . $val['costPrice'] . '</td>';
            echo '<td>'; if (isset($val['benefice'])) echo $val['benefice']; echo '</td>';        
            echo '</tr>';
        }
        echo '</table>';
        echo '<br />'; 
        echo 'Current:<br />' .
            'quantity: ' . $this->_calc['shares'] . ', ' .
            'val moy: ' . $this->_calc['val_moy'] . ', ' .
            'total transac: ' . $this->_calc['total_transac'] . ', ' .
        	'valeur actuelle: ' . $this->_lastUpdate['price'] . ', ' .
            'total valorise: ' . $this->_calc['total_valorise'] . ', ' .
            'plus value: ' . $this->_calc['plus_value'] . '<br />';
        echo '<br />';    
        echo "Total transac: " . $this->_calc['total_transac'] . "<br />";
        echo "Total valorise: " . $this->_calc['total_valorise'] . "<br />";
        echo "Plus value: " . $this->_calc['plus_value'] . "<br />";
        echo "Plus value latente: " . $this->_calc['plus_value_latente'] . "<br />";
        echo "Plus value latente pma: " . $this->_calc['plus_value_latente_pma'] . "<br />";
        echo "Prix moyen achat: " . $this->_calc['costPrice'] . "<br />";
    }
    
    public function getCurrentInfos()
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
    }
    
    public function getHistory()
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
        return ($this->_history);
    }
    
    public function getActiveHistory()
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
        $dates = $this->getTreasury()->getTreasuryDates();
        $activeStocks = array();
        foreach ($this->_history as $transac)
        {
            if ($transac['date'] >= $dates['startDate'] && $transac['date'] <= $dates['endDate'])
                $activeStocks[] = $transac;
        }
        return ($this->_history); /* TODO : finish get Active History */
        return ($activeStocks);
    }
    
    public function getStats()
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
            
        if (!$this->_calc)
            $this->calculateAll();
        return ($this->_calc);
    }
    
    public function getStockInfos()
    {
        
    }
    
    /**
     * 
     * Function that check if the stock is active (some shares are remaining).
     */
    public function isActive()
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
        $stats = $this->getStats();
        if ($stats['shares'] === 0.0)
            return (false);
        return (true);
    }
    
    public function setXXX()
    {
        
    }
    
    private function getInsertDate($date, &$before, &$after)
    {
        $before = null;
        $after = null;
        foreach ($this->_history as $val)
        {
            if ($date > $val['date']) /* TODO Use Id instead */
                $before = $val;
            else if (!$after)
               $after = $val;
        }
    }
    
    private function idIsInList($list, $id)
    {
        foreach ($list as $ind => $val)
        {
            if ($val['id'] == $id)
                return ($ind);
        }
        return (-1);
    }
    
    static function sortTransacByDate($t1, $t2)
    {
        if ($t1['date'] == $t2['date'] && $t1['id'] < $t2['date'])
            return (-1);
        else if ($t1['date'] == $t2['date'] && $t1['id'] > $t2['date'])
            return (1);
        else if ($t1['date'] < $t2['date'])
            return (-1);
        else
            return (1);
    }
    
    private function countShares($transactions)
    {
        $shares = 0;
        foreach ($transactions as $ind => $val)
        {
            if ($val['ope'] === "buy")
                $shares += $val['shares'];
            else if ($val['ope'] === "sell")
                $shares -= $val['shares'];
            if ($shares < 0)
                return ($ind);
        }
        return (self::ENOUGH_SHARES);
    }
    
    /**
     * Add a sell event to the portfolio
     * @param array $inputData
     * $inputData include :<br />
     * 'price' : positive float<br />
     * 'date' : std DB format, not in the future<br />
     * 'shares' : positive integer<br />
     * 'fees' : transaction fees<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Call the treasury to insert the transaction's information
     * 
     * Checks :<br />
     * Error if sell before buy
     * Error if not enough shares before/after
     * 
     * Before  	X		X		O		O
     * After	X		O		X		O
     * Action	Exit	Exit	CalcAll	CalcTo + Insert/CalcAll					
     * 							Count	CountTo + Insert/Count
     */
    public function addSell($data)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'addSell'))
            throw new Ivc_Acl_Exception;

        $before = null;
        $after = null;
        $this->getInsertDate($data['date'], $before, $after);
        
        if (!$before)
        {
            $this->getMessageInstance()->push(Ivc_Message::ERROR, Model_Portfolio_Portfolio::MUST_BUY_FIRST);
            return($this);
        }
        $ind = 0;
        if ($before && !$after)
        {
            $this->calculateAll();
            if ($this->_calc['shares'] < $data['shares'])
                $this->getMessageInstance()->push(Ivc_Message::ERROR, Model_Portfolio_Portfolio::NOT_ENOUGH_SHARES . " after last");
        }
        else
        {
            $calcData = $this->calculateToId($before['id']);
            if ($calcData['shares'] < $data['shares'])
                $this->getMessageInstance()->push(Ivc_Message::ERROR, Model_Portfolio_Portfolio::NOT_ENOUGH_SHARES . " before");
            $backupHistory = $this->_history;
            $this->_history[] = array('date' => $data['date'],
		    						'ope' => 'sell',
		    						'price' => floatval($data['price']),
		                            'shares' => floatval($data['shares']),
		                            'fees' => floatval($data['fees']),
		                            'id' => self::MAX_UN_INT32);
            usort($this->_history, array("Model_PortfolioShare", "sortTransacByDate"));
            $this->calculateAll();
            $this->_history = $backupHistory;
            if ($this->_calc['shares'] < 0)
                $this->getMessageInstance()->push(Ivc_Message::ERROR, Model_Portfolio_Portfolio::NOT_ENOUGH_SHARES_REMAINING);
        }  
        
        $data['stock_id'] = $this->_stockId;
        if (!$this->getMessageInstance()->getLevel())
        {
            $this->getTreasury()->addSell($data);
            Ivc_Cache::getInstance()->remove(Ivc_Cache::SCOPE_CLUB, 'portfolioList');
            /* TODO Check for other cache */
        }
        return ($this);     
    }
    
    private function updateTransac($data)
    {
    	foreach ($this->_history as $ind => $val)
    	{
    		if ($val['id'] == $data['transaction_id'])
    		{
				$val['price'] = $data['price'];
				$val['shares'] = $data['shares'];
				$val['fees'] = $data['fees'];
				return;
     		}
    	}
    	throw new Ivc_Exception(Model_Portfolio_Portfolio::NO_SUCH_ID, Zend_Log::WARN);
    }
    
    public function editSell($data)
    {
    	if (!$this->getAcl()->ivcAllowed($this, 'editSell'))
    		throw new Ivc_Acl_Exception;
    	
    	foreach ($this->_history as $ind => $val)
    	{
    		if ($val['id'] == $data['transaction_id'])
    			$data['date'] = $val['date'];
    	}
    	
    	$before = null;
    	$after = null;
    	$this->getInsertDate($data['date'], $before, $after);
    	
    	$ind = 0;
    	if ($before && !$after)
    	{
    		$this->calculateAll();
    		if ($this->_calc['shares'] < $data['shares'])
    			$this->getMessageInstance()->push(Ivc_Message::ERROR, Model_Portfolio_Portfolio::NOT_ENOUGH_SHARES . " after last");
    	}
    	else
    	{
    		$calcData = $this->calculateToId($before['id']);
    		if ($calcData['shares'] < $data['shares'])
    			$this->getMessageInstance()->push(Ivc_Message::ERROR, Model_Portfolio_Portfolio::NOT_ENOUGH_SHARES . " before");
    		$backupHistory = $this->_history;
    		$this->updateTransac($data);
    		$this->calculateAll();
    		$this->_history = $backupHistory;
    		if ($this->_calc['shares'] < 0)
    			$this->getMessageInstance()->push(Ivc_Message::ERROR, Model_Portfolio_Portfolio::NOT_ENOUGH_SHARES_REMAINING);
    	}
    	
    	$data['stock_id'] = $this->_stockId;
    	if (!$this->getMessageInstance()->getLevel())
    	{
    		$this->getTreasury()->editSell($data);
    		Ivc_Cache::getInstance()->remove(Ivc_Cache::SCOPE_CLUB, 'portfolioList');
    		/* TODO Check for other cache */
    	}
    	return ($this);
    }
    
    /**
     * Dell a sell event from the portfolio
     * @param array $inputData
     * $inputData include :<br />
     * 'id' : the stock_id<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Call the treasury to insert the transaction's information
     * 
     * Checks :<br />
     * Error if id doesn't exist
     * Error if id doesn't match operation
     * 
     * Before  	X		X		O		O
     * After	X		O		X		O
     * Action	N/A		N/A		Ok		Ok					
     * 
     */
    public function delSell($data)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'delSell'))
            throw new Ivc_Acl_Exception;           
        $ind = 0;
        if (($ind = $this->idIsInList($this->_history, $data['transaction_id'])) === self::NOT_IN_LIST)
            throw new Ivc_Exception(Model_Portfolio_Portfolio::NO_SUCH_ID, Zend_Log::WARN);
        if ($this->_history[$ind]['ope'] !== 'sell')
            throw new Ivc_Exception(Model_Portfolio_Portfolio::ID_DOT_NOT_MATCH, Zend_Log::WARN);
           
        $this->getTreasury()->delSell($data);
        Ivc_Cache::getInstance()->remove(Ivc_Cache::SCOPE_CLUB, 'portfolioList');
        return ($this);
    }
    
    public function editBuy($data)
    {
    	if (!$this->getAcl()->ivcAllowed($this, 'editBuy'))
    		throw new Ivc_Acl_Exception;
    	
    	$data['stock_id'] = $this->_stockId;
    	$this->getTreasury()->editBuy($data);
    	return ($this);
    }
    
    /**
     * Add a buy event to the portfolio
     * @param array $inputData
     * $inputData include :<br />
     * 'price' : positive float<br />
     * 'date' : std DB format, not in the future<br />
     * 'shares' : positive integer<br />
     * 'fees' : transaction fees<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Call the treasury to insert the transaction's information
     * 
     * Checks :<br />
     *
     * Before  	X		X		O		O
     * After	X		O		X		O
     * Action	Ok		Ok		Ok		Ok
     */
    public function addBuy($data)
    {
    	//echo "PORT Add Buy<br />";
        if (!$this->getAcl()->ivcAllowed($this, 'addBuy'))
            throw new Ivc_Acl_Exception;

        $data['stock_id'] = $this->_stockId;
        $this->getTreasury()->addBuy($data);
        Ivc_Cache::getInstance()->remove(Ivc_Cache::SCOPE_CLUB, 'portfolioList');
        return ($this);
    }
    
    /**
     * Dell a buy event from the portfolio
     * @param array $inputData
     * $inputData include :<br />
     * 'id' : the stock_id<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Call the treasury to insert the transaction's information
     * 
     * Checks :<br />
     * Error if id doesn't exist
     * Error if id doesn't match operation
     * Error if not enough shares
     * 
     * Before  	X		X			O		O
     * After	X		O			X		O
     * Action	Ok		Del/Count	Ok		Del/Count					
     * 			(Del/Count but no loop !)
     * 								(Del/Count but useless)	
     */
    public function delBuy($data)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'delBuy'))
            throw new Ivc_Acl_Exception;
        $ind = 0;
        if (($ind = $this->idIsInList($this->_history, $data['transaction_id'])) === self::NOT_IN_LIST)
            throw new Ivc_Exception(Model_Portfolio_Portfolio::NO_SUCH_ID, Zend_Log::WARN);
        if ($this->_history[$ind]['ope'] !== 'buy')
            throw new Ivc_Exception(Model_Portfolio_Portfolio::ID_DOT_NOT_MATCH, Zend_Log::WARN);
            
        $transacs = $this->_history;
        unset($transacs[$ind]);
        $transacs = array_values($transacs);
        if ($this->countShares($transacs) !== self::ENOUGH_SHARES)
        {
            $this->getMessageInstance()->push(Ivc_Message::ERROR, Model_Portfolio_Portfolio::NOT_ENOUGH_SHARES);
            return($this);
        }
        $this->getTreasury()->delBuy($data);
        Ivc_Cache::getInstance()->remove(Ivc_Cache::SCOPE_CLUB, 'portfolioList');
        return ($this);
    }
}

/**
 * Portfolio class take care of the club's transaction.
 * It communicates with the treasury to register buy and sell actions.
 * The portfolio calculate transaction, transaction statistics and curency exchanges.
 * It also commumicate with the stock manager to register new stocks and get lasts updates.
 * 
 * @author		Jonathan Hickson
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Portfolio
 */
class Model_Portfolio_Portfolio extends Ivc_Core
{
    const ACCESS_DENIED = 'accessDenied'; //log
    const INVALID_SYMBOL = 'Ce symbole n\'est pas reconnu.';
    const NO_SUCH_ID = 'noSuchId'; //log
    const NO_SUCH_SYMBOL = 'noSuchSymbol'; // log
    const NOT_ENOUGH_SHARES = 'Vous n\'avez pas assez d\'actions dans votre portefeuille';
    const NOT_ENOUGH_SHARES_REMAINING = 'Vous n\'avez pas assez d\'actions dans votre portefeuille';
    const WRONG_PARAM = 'wrongParam'; // log
	const MUST_BUY_FIRST = 'mustBuyFirst'; // should not happen
	const ID_DOT_NOT_MATCH = 'idDoNotMatch'; // log
	
    private $_list = null;
    private $_mapper = null;
    
    public function __construct(array $options = null)
    {
    	$cs = Zend_Registry::get('Construct_stats');
    	$cs['portfolio'] += 1;
    	Zend_Registry::set('Construct_stats', $cs);
    	
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
        return ('club:' . $this->getClubId() . ':portfolio');
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
        	$acl->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, 'list')
            	->allow(Ivc_Acl::CLUB_MEMBER . $this->getClubId(), $this, null) // just for dev
            //	->allow(Ivc_Acl::CLUB_ADMIN, $this, null)
            //	->deny(Ivc_Acl::CLUB_PRESIDENT, $this, 'addSell') // test
            //	->allow(Ivc_Acl::CLUB_ADMIN, $this, 'viewStock') // test
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'addBuy')
            	->allow(Ivc_Acl::CLUB_TREASURER . $this->getClubId(), $this, 'addSell');
        }
        // Set dynamic rules
        Ivc_Acl_Factory::setDynAcl($acl, $this);
    }
    
    private function getMapper()
    {
        if (!$this->_mapper)
            $this->_mapper = new Model_Portfolio_PortfolioMapper($this->getClubId());
        return ($this->_mapper);
    }
	
	public function setTreasuryRef($treasuryRef)
    {
        $this->_treasury = $treasuryRef;
        return ($this);
    }
    
    private function getList()
    {
    	if (!$this->_list)
            $this->initStocks();
        return ($this->_list);
    }
    
    /**
     * initialize portfolio data. portfolio's stocks cache is done in this method
     * 
     */
    private function initStocks()
    {
        // Query !
        // foreach => new shares() : cafay
        // Set treasury and mapper ref
        /*
        $data = array('history' => array (
								    array('date' => '2011-02-06', 'ope' => 'buy', 'price' => 308, 'shares' => 5, 'fees' => 10),
						            array('date' => '2011-02-09', 'ope' => 'sell', 'price' => 310, 'shares' => 2, 'fees' => 0),
                                	array('date' => '2011-02-15', 'ope' => 'buy', 'price' => 306, 'shares' => 3, 'fees' => 0),
                                    array('date' => '2011-02-18', 'ope' => 'sell', 'price' => 312, 'shares' => 4, 'fees' => 10),
                                    array('date' => '2011-02-15', 'ope' => 'buy', 'price' => 308, 'shares' => 10, 'fees' => 8)
                                 ),
                      'lastUpdate' => array('price' => 308, 'up' => 314, 'high' => 308),
                      // config test
                      'config' => array('periode_start' => '2011-02-01', 'periode_end' => '2011-02-28'), // can use ref ?
                      'name' => 'ILD.PA', 'currency' => 'EUR', 'id' => 42,
                      );
        */
        
        /* TODO : Cache here not good ! */
        //$cache = Ivc_Cache::getInstance();
        //if (($this->_list = $cache->load(Ivc_Cache::SCOPE_CLUB, 'portfolioList')) === false)
        //{
            $this->_list = array();
            // Fetch portfolio infos
            $row_data = $this->getMapper()->fetchPortfolio();
            // Create shares blocks
            foreach ($row_data as $key => $data)
                $this->_list[$key] = new Model_PortfolioShare(array('data' => $data,
                													'clubId' => $this->getClubId()));
            //$cache->save($this->_list, Ivc_Cache::SCOPE_CLUB, 'portfolioList');
            //echo serialize($this->_list);
        //}
        // Link with mapper and treasury, Fetch and set last values
        foreach ($this->_list as $key => $val)
        {
            $stockManager = new Model_Portfolio_Stocks(array('stock_id' => $this->_list[$key]->getStockId()));
			$stockManager->getStock();
			$stock = $stockManager->toArray();
			if ($stock['is_default'])
			{
            	$quoteLive = new Model_Portfolio_QuotesLive(array("symbol" => $this->_list[$key]->getSymbol())); // TODO Add cache or async update limit update every 5 min
        		$quoteLive->getQuote();
        		$live = $quoteLive->toArray();
            	$this->_list[$key]->setLastUpdate(array('price' => $live['last_trade'], 'up' => 314, 'high' => 308));
			}
			else
			{
				$this->_list[$key]->setLastUpdate();
			}
        }
    }
    
    private function initStocksToDate()
    {
    	$this->_list = array();
    	$row_data = $this->getMapper()->fetchPortfolioToDate($this->getDate());
    	foreach ($row_data as $key => $data)
    		$this->_list[$key] = new Model_PortfolioShare(array('data' => $data,
    				'clubId' => $this->getClubId()));
    	foreach ($this->_list as $key => $val)
    	{
    		$stockManager = new Model_Portfolio_Stocks(array('stock_id' => $this->_list[$key]->getStockId()));
    		$stockManager->getStock();
    		$stock = $stockManager->toArray();
    		if ($stock['is_default'])
    		{
    			$quoteLive = new Model_Portfolio_QuotesLive(array("symbol" => $this->_list[$key]->getSymbol())); // TODO Add cache or async update limit update every 5 min
    			$quoteLive->getQuote();
    			$live = $quoteLive->toArray();
    			$this->_list[$key]->setLastUpdate(array('price' => $live['last_trade'], 'up' => 314, 'high' => 308));
    		}
    		else
    			$this->_list[$key]->setLastUpdate();
    	}
    }
    
    /**
     * Get the portfolio actives stocks. This method is called to get all stocks history
     * 
     */
    public function getPortfolioStocks()
    {

        if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
        if (!$this->_list)
            $this->initStocks();
        return ($this->_list);
    }
    
    /**
     * Get the portfolio actives stocks. This method is called to display the portfolio active state
     * 
     */
    public function getPortfolioActiveStocks()
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
        if ($this->_list === null)
            $this->initStocks();
        $activeList = array();
        foreach ($this->_list as $id => &$val)
        {
            if ($val->isActive())
                $activeList[$id] = $val;
        }
        return ($activeList);
    }
    
    /**
     * Get the portfolio actives stocks symbols.
     * 
     */
    public function getPortfolioActiveStocksSymbols()
    {
        if (!$this->getAcl()->ivcAllowed($this, 'list'))
            throw new Ivc_Acl_Exception;
        if ($this->_list === null)
            $this->initStocks();
        $activeList = array();
        foreach ($this->_list as $id => &$val)
        {
            if ($val->isActive())
                $activeList[] = $val->getSymbol();
        }
        return ($activeList);
    }
    
    private function setTransacAction(&$transacs)
    {
        foreach ($transacs as &$val)
        {
            if ($val['ope'] === 'buy' && $this->getTreasury()->checkDate($val['date']) === 0)
                $val['action'] = $this->checkAcls(array('delBuy', 'editBuy'));
            else if ($val['ope'] === 'sell' && $this->getTreasury()->checkDate($val['date']) === 0)
                $val['action'] = $this->checkAcls(array('delSell', 'editSell'));
            else if ($val['ope'] === 'dividend' && $this->getTreasury()->checkDate($val['date']) === 0)
                $val['action'] = $this->checkAcls(array('delDividend', 'editDividend'));
        }
        return ($transacs);
    }
    
    /**
     * 
     * Get the club's portfolio, calcul statistics, convert currency, and get last stock prices.
     * It also manage caching to provide a good speed boost at display.
     */
    public function getData()
    {
        if (!$this->getAcl()->ivcAllowed($this, 'viewStock'))
            throw new Ivc_Acl_Exception;
        $cache = Ivc_Cache::getInstance();
        /* TODO : clear cache */
        if (($portfolio = $cache->load(Ivc_Cache::SCOPE_CLUB, 'portfolio')) === false) // 5 min ?
        {
            $totalPortfolio = 0;
            $totalGain = 0;
            $totalMarketValue = 0;
            $data = array();
            foreach ($this->getPortfolioStocks() as $id => $stock)
            {
                if ($stock->isActive()) // activate calculateAll ...
                {
                    $stats = $stock->getStats();
                    $lastUpdate = $stock->getLastUpdate();
                    $data[] = array('symbol' => $stock->getSymbol(),
                                    'name' => $stock->getName(),
                                    'currency' => $stock->getCurrency(),
                                    'shares' => $stats['shares'],
                                    'lastPrice' => $lastUpdate['price'],
                                    'costPrice' => $stats['costPrice'],
                                    'marketValue' => ($lastUpdate['price'] * $stats['shares']),
                                    'virtualGain' => $stats['plus_value_latente'],
                                    'virtualGainP' => ($stats['plus_value_latente'] / ($stats['costPrice'] * $stats['shares']) * 100),
                                    'stockId' => $id,
                                    'ActiveHistory' => $this->setTransacAction($stock->getActiveHistory()),
                                    'open' => true,
                                    'action' => $this->checkAcls(array('addBuy', 'addSell', 'addDividend')),
                                    'view' => $this->checkAcls(array('viewStockHistory', 'viewGraph')));
                    $totalPortfolio += $stats['costPrice'] * $stats['shares'];
                    $totalMarketValue += $lastUpdate['price'] * $stats['shares'];
                    $totalGain += $stats['plus_value_latente'];
                }
            }
            foreach ($data as &$stock)
            {
                $stock['weight'] = ($stock['costPrice'] * $stock['shares']) / $totalPortfolio * 100;
            }
            if ($totalPortfolio)
            	$totalVirtualGainP = $totalGain / $totalPortfolio * 100;
            else
            	$totalVirtualGainP = 0;
            $portfolioStats = array('totalCostPrice' => $totalPortfolio,
                                    'totalMarketValue' => $totalMarketValue,
                                    'totalVirtualGain' => $totalGain,
                                    'totalVirtualGainP' => ($totalVirtualGainP),
            );
            $portfolio = array('portfolio' => $data, 'stats' => $portfolioStats);
            $cache->save($portfolio, Ivc_Cache::SCOPE_CLUB, 'portfolio'); /* TODO : set ACL outside of cache */
        }
        return($portfolio);
    }
    
    public function getTransacById($id)
    {
    	$data = $this->getData();
    	foreach ($data['portfolio'] as $stock)
    	{
    		foreach ($stock['ActiveHistory'] as $transac)
    		{
    			if ($transac['id'] == $id)
    			{
    				$out = array('date' => $transac['date'],
    							 'shares' => $transac['shares'],
    							 'price' => $transac['price'],
    							 'fees' => $transac['fees']);
    				return ($out);
    			}
    		}	
    	}
    	throw new Ivc_Exception(Model_Portfolio_Portfolio::NO_SUCH_ID, Zend_Log::WARN);
    	return (null);
    }
    
    private function isInList($name)
    {
        foreach ($this->getPortfolioStocks() as $id => $val)
        {
            if ($val->getSymbol() === $name)
                return ($id);
        }
        return (-1);
    }
    
    /**
     * Add a buy event to the portfolio
     * @param array $inputData
     * $inputData include :<br />
     * 'symbol' : symbol of the stock that must be bough<br />
     * 	OR 'stock_id' : stock id of the stock that must be bough<br />
     * 'price' : positive float<br />
     * 'date' : std DB format, not in the future<br />
     * 'shares' : positive integer<br />
     * 'fees' : transaction fees<br />
     * 'currency' : stock currency<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Call the treasury to insert the transaction's information
     * 
     * Checks :<br />
     * Warning if the symbol provided by the user is not valid (no automatic update possible)
     */
    public function addBuy($data)
    {
        if (!$this->getAcl()->ivcAllowed($this, 'addBuy'))
            throw new Ivc_Acl_Exception;
        // price negative remise a 0
        if (isset($data['stock_id']) AND $data['stock_id']) {
            $stocks = $this->getPortfolioStocks();
            if (isset($stocks[intval($data['stock_id'])])) {
                $stocks[intval($data['stock_id'])]->addBuy($data);
            } else
                throw new Ivc_Exception(self::NO_SUCH_ID, Zend_Log::WARN);
            $this->_list = null; // Clear list
            return ($this);
        }
        if (isset($data['symbol']) && isset($data['currency'])) {
            if (($id = $this->isInList($data['symbol'])) !== - 1) {
                $this->_list[$id]->addBuy($data);
            } else {
                $quoteManager = new Model_Portfolio_Stocks(array('symbol' => $data['symbol'], 
                	  									   'currency' => $data['currency']));
                                                           $force = false;
                if (isset($data['force']) && $data['force'] == true)
                    $force = true;
                if (! $force && ! $quoteManager->isValidStock())
                    $this->getMessageInstance()->push(Ivc_Message::WARNING, self::INVALID_SYMBOL);
                else {
                    $stock = $quoteManager->getStock(); // Add quote to db
                    $newShare = new Model_PortfolioShare(
                    array('stockId' => $stock->stock_id));
                    //$newShare->setTreasuryRef($this->getTreasury());
                    $newShare->addBuy($data);
                }
                
            }
            $this->_list = null; // Clear list
            return ($this);
        }
        throw new Ivc_Exception(self::WRONG_PARAM, Zend_Log::WARN);
    }
    
    public function deleteTransaction($data)
    {
       if (!($this->getAcl()->ivcAllowed($this, 'delBuy') || $this->getAcl()->ivcAllowed($this, 'delSell')))
           throw new Ivc_Acl_Exception;
       
       if (isset($data['stock_id'])) {
       	$data['transaction_id'] = $data['stock_id'];  // Controler send stockId instead of transacId
       	$tmp = $this->getMapper()->findStockTransacDataFromTransactionId($data['stock_id']);
       	if ($tmp == null)
       		throw new Ivc_Exception(self::NO_SUCH_ID, Zend_Log::WARN);
       	$data['stock_id'] = $tmp['stock_id']; /* TODO : this is not the nice way to do it. Same for next line ... */
       	$data['ope'] = $tmp['type'];
       	$stocks = $this->getPortfolioStocks();
       	if (isset($stocks[intval($data['stock_id'])])) {
       		$stocks[intval($data['stock_id'])]->{"del" . ucfirst($data['ope'])}($data);
       	} else
       		throw new Ivc_Exception(self::NO_SUCH_ID, Zend_Log::WARN);
       	return ($this);
       }
       /*
       if (isset($data['stock_id'])) {
            $stocks = $this->getPortfolioStocks();
            if (isset($stocks[intval($data['stock_id'])])) {
                $stocks[intval($data['stock_id'])]->{"del" . ucfirst($data['ope'])}($data);
            } else
                throw new Ivc_Exception(self::NO_SUCH_ID, Zend_Log::WARN);
            return ($this);
        }
        if (isset($data['symbol']) && isset($data['currency'])) {
            if (($id = $this->isInList($data['symbol'])) !== - 1) {
                $this->_list[$id]->delBuy($data);
            } else {
                throw new Ivc_Exception(self::NO_SUCH_SYMBOL, Zend_Log::WARN);
            }
            return ($this);
        }
        */
        throw new Ivc_Exception(self::WRONG_PARAM, Zend_Log::WARN); 
    }
 
    public function addEdit($data)
    {
    	if (!($this->getAcl()->ivcAllowed($this, 'editBuy') || $this->getAcl()->ivcAllowed($this, 'editSell')))
    		throw new Ivc_Acl_Exception;
    
    	if (isset($data['stock_id'])) {
    		$data['transaction_id'] = $data['stock_id'];  // Controler send stockId instead of transacId
    		$tmp = $this->getMapper()->findStockTransacDataFromTransactionId($data['stock_id']);
    		if ($tmp == null)
    			throw new Ivc_Exception(self::NO_SUCH_ID, Zend_Log::WARN);
    		$data['stock_id'] = $tmp['stock_id']; /* TODO : this is not the nice way to do it. Same for next line ... */
    		$data['ope'] = $tmp['type'];
    		$data['old_shares'] = $tmp['shares'];
    		$data['old_price'] = $tmp['price'];
    		$data['old_fees'] = $tmp['fees'];
    		$data['date'] = $tmp['date'];
    		$stocks = $this->getPortfolioStocks();
    		if (isset($stocks[intval($data['stock_id'])])) {
    			$stocks[intval($data['stock_id'])]->{"edit" . ucfirst($data['ope'])}($data);
    		} else
    			throw new Ivc_Exception(self::NO_SUCH_ID, Zend_Log::WARN);
    		return ($this);
    		 
    		//$data['transacId'] = $data['stockId']; // Controler send stockId instead of transacId
    		$this->getMessageInstance()->push(Ivc_Message::SUCCESS, "ok");
    	}
    }
    
    /**
     * Add a sell event to the portfolio
     * @param array $inputData
     * $inputData include :<br />
     * 'symbol' : symbol of the stock that must be sold<br />
     * OR 'stock_id' : stock id of the stock that must be sold<br />
     * 'price' : positive float<br />
     * 'shares' : quantity, positive integer<br />
     * 'date' : std db format, not in the future<br />
     * 'fees' : transaction fees<br />
     * ['force'] : flag to bypass Warnings<br />
     * 
     * Call the treasury to insert the transaction's information
     * 
     * Checks :<br />
     * Error if the symbol provided is not in the portfolio
     * Error if not enough shares
     */
    public function addSell($data)
    {
    	if (!$this->getAcl()->ivcAllowed($this, 'addSell'))
    		throw new Ivc_Acl_Exception;
    	if (isset($data['stock_id'])) {
    		$stocks = $this->getPortfolioStocks();
    		if (isset($stocks[intval($data['stock_id'])])) {
    			$stocks[intval($data['stock_id'])]->addSell($data);
    		} else
    			throw new Ivc_Exception(self::NO_SUCH_ID, Zend_Log::WARN);
    		$this->_list = null; // Clear list
    		return ($this);
    	}
    	if (isset($data['symbol']) && isset($data['currency'])) {
    		if (($id = $this->isInList($data['symbol'])) !== - 1) {
    			$this->_list[$id]->addSell($data);
    		} else {
    			throw new Ivc_Exception(self::NO_SUCH_SYMBOL, Zend_Log::WARN);
    		}
    		$this->_list = null; // Clear list
    		return ($this);
    	}
    	throw new Ivc_Exception(self::WRONG_PARAM, Zend_Log::WARN);
    }
    
    public function getReevaluationData($date = null)
    {
    	if ($date !== null)
    	$this->setDate($date);
		$this->getTreasury()->setDate($date);
    	$this->initStocksToDate();
    	
    	$stocks = $this->getPortfolioActiveStocks();
    	
    	$list = array();
    	foreach ($stocks as $id => $stock)
    	{
    		//Zend_Debug::dump($stock);
    		$stats = $stock->getStats();
    		$lastUpdate = $stock->getLastUpdate();
    		$list[$id] = array('name' => $stock->getSymbol(), 'shares' => $stats['shares'], 'costPrice' => $stats['costPrice'], 'lastPrice' => $lastUpdate['price']);
    	}
    	return ($list);
    }
    
    public function addReevaluation($data)
    {
    	$this->getTreasury()->addReevaluation($data);
    }

    public function getReevaluationList()
    {
    	$list = array();
    	$trList = $this->getTreasury()->getTreasuryPendingValidationList();
		
    	foreach ($trList as $date => $id)
    		$list[] = $date;
    	return ($list);
    	/*
    	SELECT *
    	FROM  `treasury_balance_sheet`
    	LEFT JOIN  `treasury_revaluation` ON treasury_balance_sheet.club_id = treasury_revaluation.club_id
    	AND treasury_balance_sheet.date = treasury_revaluation.date*/
    }
    
}
