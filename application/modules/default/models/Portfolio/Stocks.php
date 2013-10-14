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
 * Stocks manager
 * 
 * Creates a link between different API such as Yahoo! Finance in order to
 * get stocks information and store them into our database. A fallback will be
 * created to handle connexion or parsing troubles with Yahoo.
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Portfolio
 * @todo		Fallback Yahoo to Euronext
 */
class Model_Portfolio_Stocks
{
    protected $_dbTable;
    protected $_dbAdapter;
    
    protected $_data = array(
        'stock_id'       => null,
        'symbol'         => null,
        'name'           => null, 
        'stock_exchange' => null, 
        'currency'       => null,
        'is_default'     => false,
        'last_update'    => null);
    
    //CONST YQL_COL_VALID = 'Symbol, Name, StockExchange';
    CONST YQL_IS_VALID = 'ErrorIndicationreturnedforsymbolchangedinvalid';
    CONST YQL_NAME = 'Name';
    CONST YQL_STOCK_EXCHANGE = 'StockExchange';
    
    public function __construct($data)
    {
        $this->populate($data);        

        if (!isset($this->stock_id) AND (!isset($this->symbol) OR !isset($this->currency))) {
            throw new Ivc_Exception('Initial data must contain a stock_id or a pair symbol/currency');
        }
        
        if (!isset($this->name) AND isset($this->symbol)) {
            $this->name = $this->symbol;
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
            $this->setDbTable('Model_Portfolio_DbTable_Stocks');
        }
        return $this->_dbTable;
    }
    
    public function getQuote($startDate = null, $endDate = null)
    {
        if (!isset($this->stock_id)) {
            throw new Ivc_Exception('stock_id must be set');
        }
        
        $this->getStock();
        $data = array('symbol' => $this->symbol);
        
        if ($startDate) {
            $endDate ?: $endDate = $startDate;
            
            $quote = new Model_Portfolio_QuotesHistorical($data);
            $start = new Zend_Date($startDate, "yyyy-MM-dd", null);
            $end = new Zend_Date($endDate, "yyyy-MM-dd", null);
            
            while ($start->compare($end, "yyyy", null)) {
                $oldStart = clone $start;
                $newEnd = $oldStart->addYear(1, "yyyy", null)->setDay(1, null)->setMonth(01);
                $quote = $quote->getQuote($start->toString("yyy-MM-dd"), $newEnd->toString("yyy-MM-dd"));
                Zend_Debug::dump(count($quote), "Results found:");
                Zend_Debug::dump($start->toString(), "start");Zend_Debug::dump($newEnd->toString(), "end");
                $start = clone $newEnd;
               echo "--------------";sleep(1);
            }
            
            Zend_Debug::dump($start->toString(), "start");Zend_Debug::dump($end->toString(), "end");
            $quote = $quote->getQuote($start->toString("yyy-MM-dd"), $end->toString("yyy-MM-dd"));
            Zend_Debug::dump(count($quote), "Results found:");
        } else {
            $quote = new Model_Portfolio_QuotesLive($data);
            $quote = $quote->getQuote();
        }
        
        return $quote;
    }
    
    /**
     * Get stock
     * 
     * @param 	object|string|array $data
     * @return	array $stock
     */
    public function getStock()
    {
        $dbTable = $this->getDbTable();
        if (isset($this->stock_id)) {
            $row = $dbTable->find($this->stock_id)->current();
        } else {
            $select = $dbTable->select();
            $select->where('symbol = ?', $this->symbol)
                   ->where('currency = ?', $this->currency);
            $row = $dbTable->fetchRow($select);
        }
        if ($row) {
            $this->populate($row);
            return $this;
        }
        
        $this->isValidStock();
        $this->saveStock();
        return $this;
    }

    public function saveStock()
    {
        $dbTable = $this->getDbTable();
        $row = $dbTable->find($this->stock_id)->current();
        if ($row) {
            foreach ($this->_data as $key => $value) {
                $row->$key = $value;
            }
            return $row->save();
        }
        $this->stock_id = $dbTable->insert($this->_data);
        return $this->stock_id;
    }
    
    public function deleteStock($stock_id = null)
    {
        if ($stock_id !== null and is_int($stock_id)) {
            $id = $stock_id;
        } elseif (! isset($this->stock_id)) {
            $id = $this->stock_id;
        }
        
        if (!isset($id)) {
            throw new Exception('stock_id must be set to delete a stock'); 
        }
        
        $dbTable = $this->getDbTable();
        $where = $dbTable->quoteInto('stock_id = ?', $this->stock_id);
        return $dbTable->delete($where);
    }
    
    public function isValidStock($stock = null)
    {
        if ($stock instanceof Model_Portfolio_Stocks) {
            $this->symbol = $stock->symbol();
        } elseif (is_array($stock)) {
            $this->symbol = $stock['symbol'];
            $this->currency = $stock['currency'];
        }
        if (!is_string($this->symbol) AND !is_string($this->currency)) {
            throw new Ivc_Exception('Invalid symbol type or no symbol saved');
        }
        
        $yql = new Zend_Rest_Client('http://query.yahooapis.com/v1/public/yql');
        $yql->q('select ' . self::YQL_IS_VALID . ',' . self::YQL_NAME . ',' . self::YQL_STOCK_EXCHANGE . 
        	   ' from yahoo.finance.quotes where symbol="' . $this->getDbAdapter()->quote(strtolower($this->symbol)) . '"');
        $yql->format('xml')->env('store://datatables.org/alltableswithkeys')->callback('');
        
        try {
            $get = $yql->get();
            $quote = $get->results->quote;
        } catch (Zend_Http_Client_Exception $e) {
            // TODO: To log > timeout or error sent by Yahoo!
            return false;
        }
        
        $filter = new Zend_Filter_StripTags();
        $stockExchange = $filter->filter((string) $quote->{self::YQL_STOCK_EXCHANGE});
        $name = $filter->filter((string) $quote->{self::YQL_NAME});
        if (!(string) $quote->{self::YQL_IS_VALID}) {
            $this->is_default = true;
            if ($name != null AND $name != "N/A") {
                $this->name = $name;
            }
            if ($stockExchange != null AND $stockExchange != "N/A") {
                $this->stock_exchange = $stockExchange;
            }
            return true;
        }
        return false;
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