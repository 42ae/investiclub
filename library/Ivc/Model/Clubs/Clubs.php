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
 * @subpackage	Clubs
 */
class Ivc_Model_Clubs_Clubs implements Iterator, Countable
{
    protected $_count;
    protected $_gateway;
    protected $_resultSet;

    public function __construct($results, $gateway)
    {
        $this->setGateway($gateway);
        $this->_resultSet = $results;
    }

    public function setGateway(Ivc_Model_Clubs_Gateway $gateway)
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
        if ($this->_resultSet instanceof Iterator) {
            $key = $this->_resultSet->key();
        } else {
            $key = key($this->_resultSet);
        }
        $result = $this->_resultSet[$key];
        if (! $result instanceof Ivc_Model_Clubs_Club) {
            $gateway = $this->getGateway();
            $result = $gateway->createUser($result);
            $this->_resultSet[$key] = $result;
        }
        return $result;
    }

    public function key()
    {
        return key($this - _resultSet);
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
}