<?php

/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category   InvestiClub
 * @copyright  Copyright (c) 2011-2013 All Rights Reserved
 * @license    http://investiclub.net/license
 */

/**
 * Brokers mapper
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Model
 * @subpackage	Clubs
 */
class Model_Clubs_BrokersMapper
{
    protected $_dbTable;

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Ivc_Model_Clubs_DbTable_Brokers');
        }
        return $this->_dbTable;
    }

    public function save(Model_Clubs_Brokers $broker)
    {
        $data = array('name'       => $broker->getName(), 
                      'url'        => $broker->getUrl(), 
                      'country'    => $broker->getCountry(), 
                      'is_default' => $broker->getDefault(), 
                      'last_update' => date('Y-m-d H:i:s'));
        
        if (null === ($id = $broker->getId())) {
            unset($data['broker_id']);
            $id = $this->getDbTable()->insert($data);
            $broker->setId($id);
        } else {
            $this->getDbTable()->update($data, array('broker_id = ?' => $id));
        }
        return $this;
    }

    public function find($id, Model_Clubs_DbTable_Brokers $broker)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $broker->setId($row->broker_id)
               ->setName($row->name)
               ->setUrl($row->url)
               ->setCountry($row->country)
               ->setDefault($row->is_default)
               ->setCreatedOn($row->created_on);
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Model_Clubs_Brokers();
            $entry->setId($row->broker_id)
                  ->setName($row->name)
                  ->setUrl($row->url)
                  ->setCountry($row->country)
                  ->setDefault($row->is_default)
                  ->setCreatedOn($row->created_on);
            $entries[] = $entry;
        }
        return $entries;
    }
}