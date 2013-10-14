<?php
/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category	Ivc
 * @package		Ivc_Model
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Object that represents a bunch of users.
 * This class extends both Iterator and Countable pattern.
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Model
 * @subpackage	Users
 */
class Ivc_Model_Users_Users implements Countable, Iterator
{
    protected $_count;
    protected $_gateway;
    protected $_resultSet = null;

    public function __construct($results, $gateway)
    {
        $this->setGateway($gateway);
        $this->populate($results);
        $this->_count = null;
    }
    
    public function setGateway(Ivc_Model_Users_Gateway $gateway)
    {
        $this->_gateway = $gateway;
        return $this;
    }

    public function getGateway()
    {
        return $this->_gateway;
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
    
    public function populate($data)
    {
        $gateway = $this->getGateway();
        if ($data instanceof Zend_Db_Table_Rowset) {
            foreach ($data as $row) {
                $this->_resultSet[$row->user_id] = $gateway->createUser($row);
            }
        } else if ($data instanceof Zend_Db_Table_Row) {
            $this->_resultSet[$data->user_id] = $gateway->createUser($data);
        }
    }
}