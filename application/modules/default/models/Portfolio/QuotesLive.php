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
 * Live quotes manager
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Portfolio
 */
class Model_Portfolio_QuotesLive
{
    protected $_dbTable;
    protected $_dbAdapter;
    
    protected $_data = array(
        'symbol'     => null,
        'last_trade' => null,
        'ask' => null,
        'bid' => null,
        'datetime' => null,
        'last_update' => null,
        );
        
    CONST YQL_LAST_TRADE = 'LastTradePriceOnly';
    CONST YQL_ASK        = 'Ask';
    CONST YQL_BID        = 'Bid';
    CONST YQL_DATE       = 'LastTradeDate';
    CONST YQL_TIME       = 'LastTradeTime';
    
    public function __construct($data)
    {
        $this->populate($data);        

        if (!isset($this->symbol)) {
            throw new Ivc_Exception('Initial data must contain a symbol');
        }
    }

    function setDbAdapter($dbAdapter = null)
    {
        if ($dbAdapter && ($dbAdapter instanceof Zend_Db_Adapter_Abstract)) {
            $this->_dbAdapter = $dbAdapter;
        } else {
            $this->_dbAdapter = Zend_Db_Table::getDefaultAdapter();
        }
        return $this;
    }

    function getDbAdapter()
    {
        if (! $this->_dbAdapter) {
            $this->setDbAdapter();
        }
        return $this->_dbAdapter;
    }

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (! $dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Ivc_Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Model_Portfolio_DbTable_QuotesLive');
        }
        return $this->_dbTable;
    }
    
    private function getCacheSymbol()
    {
    	$str = $this->symbol;
    	$str = str_replace('.', '_', $str);
    	return ($str);
    }
    
    public function getQuote()
    {
        // if in cache - return cache sinon:
    	$cache = Ivc_Cache::getInstance();
    	$tmpData = $cache->load(Ivc_Cache::SCOPE_IVC, 'quoteLive' . $this->getCacheSymbol());
    	if ($tmpData != false && !defined('BATCH_MODE')) {
    		$this->_data = $tmpData;
    		return ($this);
    	}
    		
    	
        $yql = new Zend_Rest_Client('http://query.yahooapis.com/v1/public/yql');
        $yql->q('select * ' . 
        	    'from yahoo.finance.quotes ' .
        		'where symbol="' . $this->getDbAdapter()->quote(strtolower($this->symbol)) . '"');
        
        $yql->format('xml')->env('store://datatables.org/alltableswithkeys')->callback('');
        
        try {
            $get = $yql->get();
            $quote = $get->results->quote;
        } catch (Zend_Http_Client_Exception $e) {
            // TODO: To log > timeout or error sent by Yahoo!
            return false;
        }
        
        $filter = new Zend_Filter_StripTags();
        $this->last_trade = $filter->filter((string) $quote->{self::YQL_LAST_TRADE});
        $this->ask        = $filter->filter((string) $quote->{self::YQL_ASK});
        $this->bid        = $filter->filter((string) $quote->{self::YQL_BID});
        $this->datetime   = $filter->filter((string) $quote->{self::YQL_DATE}) . ' ';
        $this->datetime  .= $filter->filter((string) $quote->{self::YQL_TIME});
        
        $dbTable = $this->getDbTable();
        $row = $dbTable->find($this->symbol)->current();
        if (!is_numeric($this->last_trade) || !is_numeric($this->ask)
        	|| !is_numeric($this->bid) || $this->datetime == ' ') { // if Yahoo send shit, load data and save to cache
        	if ($row) {
        		foreach ($this->_data as $key => $value) {
        			$this->_data[$key] = $row->$key;
        		}
        		$cache->save($this->_data, Ivc_Cache::SCOPE_IVC, 'quoteLive' . $this->getCacheSymbol(), 300);
        		return $this;
        	}
        	else
        		return false;
        }

        $date = new Zend_Date();
        $date->setTimezone("America/New_York");
        $date->set($this->datetime, "M/d/yyyy hh:mma", null);
        $date->setTimezone("UTC");
        $this->datetime = $date->getIso();

        if ($row) {
            foreach ($this->_data as $key => $value) {
                $row->$key = $value;
            }
            $row->save();
            $cache->save($this->_data, Ivc_Cache::SCOPE_IVC, 'quoteLive' . $this->getCacheSymbol(), 300);
            return $this;
        }
        $dbTable->insert($this->_data);
       	$cache->save($this->_data, Ivc_Cache::SCOPE_IVC, 'quoteLive' . $this->getCacheSymbol(), 300);
        return $this;
    }
    
    public function populate($data)
    {
        if ($data instanceof Zend_Db_Table_Row_Abstract) {
            $data = $data->toArray();
        } elseif (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            throw new Exception('Initial data must be an array or object');
        }
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }
    
    public function toArray() {
        return $this->_data;
    }
    
    public function __set($name, $value)
    {
        if (! array_key_exists($name, $this->_data)) {
            throw new Exception('Invalid property \"' . $name . '\"');
        }
        $this->_data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }
        return null;
    }

    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    public function __unset($name)
    {
        if (isset($this->$name)) {
            $this->_data[$name] = null;
        }
    }
}