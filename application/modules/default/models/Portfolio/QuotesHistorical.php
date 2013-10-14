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
 * Historical quotes manager
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Portfolio
 */
class Model_Portfolio_QuotesHistorical implements Iterator, Countable
{
    protected $_dbTable;
    protected $_dbAdapter;

    protected $_resultSet;
    protected $_count;
    protected $_data = array(
        'symbol'     => null,
        'date' => null,
        'open' => null,
        'close' => null,
        'high' => null,
        'low' => null,
        'volume' => null,
        'last_update' => null,
        );
        
    CONST YQL_DATE   = 'Date';
    CONST YQL_OPEN   = 'Open';
    CONST YQL_CLOSE  = 'Close';
    CONST YQL_HIGH   = 'High';
    CONST YQL_LOW    = 'Low';
    CONST YQL_VOLUME = 'Volume';
    
    public function __construct($data)
    {
        $this->populate($data);  
        $this->_resultSet = array();      

        if (!isset($this->symbol)) {
            throw new Ivc_Exception('Initial data must contain a stock_id');
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
            $this->setDbTable('Model_Portfolio_DbTable_QuotesHistorical');
        }
        return $this->_dbTable;
    }
    
    public function getQuote($startDate, $endDate)
    {
        // if in cache - return cache sinon: <= very important !
        //Zend_Debug::dump($startDate, "Start:");
        //Zend_Debug::dump($endDate, "End:");
        $yql = new Zend_Rest_Client('http://query.yahooapis.com/v1/public/yql');
        $yql->q('select * ' . 
        	    'from yahoo.finance.historicaldata ' .
        		'where symbol=' . $this->getDbAdapter()->quote(strtolower($this->symbol)) . ' ' .
                'and startDate="' . $startDate . '" ' .
                'and endDate="' . $endDate . '"');
        
        $yql->format('xml')->env('store://datatables.org/alltableswithkeys')->callback('');

        try {
            $get = $yql->get();
            $quotes = $get->results->quote;
        } catch (Zend_Http_Client_Exception $e) {
            // TODO: To log > timeout or error sent by Yahoo!
            return false;
        }
        
        foreach ($quotes as $k => $quote) {
            
            $filter = new Zend_Filter_StripTags();
            $this->date   = $filter->filter((string) $quote->{self::YQL_DATE});
            $this->open   = $filter->filter((string) $quote->{self::YQL_OPEN});
            $this->close  = $filter->filter((string) $quote->{self::YQL_CLOSE});
            $this->high   = $filter->filter((string) $quote->{self::YQL_HIGH});
            $this->low    = $filter->filter((string) $quote->{self::YQL_LOW});
            $this->volume = $filter->filter((string) $quote->{self::YQL_VOLUME});
    
            $date = new Zend_Date();
            $date->setTimezone("America/New_York"); // yahoo timezone
            $date->set($this->date, "yyyy-MM-dd", null);
            $date->setTimezone("UTC");
            $this->date = $date->toString("yyyy-MM-dd");
    
            $dbTable = $this->getDbTable();
            $row = $dbTable->find($this->symbol, $this->date)->current();
            if ($row) {
                foreach ($this->_data as $key => $value) {
                    $row->$key = $value;
                }
                $row->save();
            } else {
                $dbTable->insert($this->_data);
            }
            
            $this->_resultSet[] = clone $this;
        }
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
    

    public function count()
    {
        if (null === $this->_count) {
            $this->_count = count($this->_resultSet);
        }
        return $this->_count;
    }

    public function current()
    {
        return current($this->_resultSet);
    }

    public function key()
    {
        
        return key($this->_resultSet);
    }

    public function next()
    {
        return next($this->_resultSet);
    }

    public function rewind()
    {
        return reset($this->_resultSet);
    }

    public function valid()
    {
        return (bool) $this->current();
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